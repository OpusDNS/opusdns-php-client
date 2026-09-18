<?php

declare(strict_types=1);

namespace OpusDNS\Client\Tests\Generator;

use OpusDNS\Client\Page;
use OpusDNS\Generator\Generator;
use PHPUnit\Framework\TestCase;

/**
 * Generates a small fixture specification and checks both the emitted source and the behaviour of the models.
 * The fixture's Model and Enum classes are autoloaded from the temporary output directory.
 */
final class GeneratorTest extends TestCase
{
    private static string $out;

    /** @var array<string, int|string> */
    private static array $report;

    public static function setUpBeforeClass(): void
    {
        self::$out = sys_get_temp_dir() . '/opusdns-generator-test-' . getmypid();
        self::$report = (new Generator())->generate(__DIR__ . '/../fixtures/fixture-spec.yaml', self::$out);

        spl_autoload_register(static function (string $class): void {
            if (preg_match('/^OpusDNS\\\\Client\\\\(Model|Enum)\\\\(Fixture[A-Za-z0-9]+)$/', $class, $match) === 1) {
                $file = self::$out . "/{$match[1]}/{$match[2]}.php";
                if (is_file($file)) {
                    require $file;
                }
            }
        });
    }

    public static function tearDownAfterClass(): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(self::$out, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );
        foreach ($iterator as $entry) {
            $entry->isDir() ? rmdir($entry->getPathname()) : unlink($entry->getPathname());
        }
        rmdir(self::$out);
    }

    private static function source(string $relative): string
    {
        return (string) file_get_contents(self::$out . '/' . $relative);
    }

    public function testReportCountsWhatWasGenerated(): void
    {
        self::assertSame('2026-01-01-000000', self::$report['spec_version']);
        self::assertSame(2, self::$report['enums']);
        self::assertSame(8, self::$report['models']);
        self::assertSame(1, self::$report['services']);
        self::assertSame(5, self::$report['operations']);
    }

    public function testEveryFileIsMarkedAsGeneratedAndParses(): void
    {
        $files = glob(self::$out . '/{*,*/*}.php', GLOB_BRACE) ?: [];
        self::assertCount((int) self::$report['files'], $files);
        foreach ($files as $file) {
            self::assertStringContainsString('generated from the OpenAPI specification', (string) file_get_contents($file));
            exec('php -l ' . escapeshellarg($file) . ' 2>&1', $output, $exit);
            self::assertSame(0, $exit, implode("\n", $output));
        }
    }

    public function testEnumsUseSafeCaseNamesAndBackingTypes(): void
    {
        $status = self::source('Enum/FixturePetStatus.php');
        self::assertStringContainsString('enum FixturePetStatus: string', $status);
        self::assertStringContainsString("case AVAILABLE = 'available';", $status);
        self::assertStringContainsString("case PENDING_ADOPTION = 'pending-adoption';", $status);
        self::assertStringContainsString("case _1D = '1d';", $status);
        self::assertStringContainsString("case HARD_BOUNCE = 'HARD-BOUNCE';", $status);

        $code = self::source('Enum/FixtureRedirectCode.php');
        self::assertStringContainsString('enum FixtureRedirectCode: int', $code);
        self::assertStringContainsString('case _301 = 301;', $code);
    }

    public function testModelConstructorReflectsRequiredNullableAndDefaults(): void
    {
        $pet = self::source('Model/FixturePet.php');
        self::assertStringContainsString('final readonly class FixturePet implements ApiModel', $pet);
        self::assertStringContainsString(" * Pet\n *\n * A pet in the shop.", $pet);
        self::assertStringContainsString('public string $id,', $pet);
        self::assertStringContainsString('public FixturePetStatus|string $status,', $pet);
        self::assertStringContainsString("status: FixturePetStatus::tryFrom(\$data['status']) ?? \$data['status'],", $pet);
        self::assertStringContainsString('public \DateTimeImmutable $createdOn,', $pet);
        self::assertStringContainsString('public ?\DateTimeImmutable $bornOn = null,', $pet);
        self::assertStringContainsString("bornOn: isset(\$data['born_on']) ? Serializer::parseDate(\$data['born_on']) : null,", $pet);
        self::assertStringContainsString("'born_on' => \$this->bornOn === null ? null : \$this->bornOn->format('Y-m-d'),", $pet);
        self::assertStringContainsString('public float $weight,', $pet);
        self::assertStringContainsString('public ?string $nickname = null,', $pet);
        self::assertStringContainsString('public array $tags = [],', $pet);
        self::assertStringContainsString('public ?FixtureOwner $owner = null,', $pet);
        self::assertStringContainsString('public ?array $attributes = null,', $pet);
        self::assertStringContainsString("attributes: isset(\$data['attributes']) ? (array) \$data['attributes'] : null,", $pet);
        self::assertStringContainsString("'attributes' => \$this->attributes === null ? null : (\$this->attributes === [] ? new \\stdClass() : \$this->attributes),", $pet);
        self::assertStringContainsString("public string \$kind = 'pet',", $pet);
        self::assertStringContainsString('public FixtureRedirectCode|int $redirectCode = FixtureRedirectCode::_301,', $pet);
        self::assertStringContainsString('public FixtureAdoptedEvent|FixtureLostEvent|null $event = null,', $pet);
        self::assertStringContainsString('@param string $id TypeID prefix: pet.', $pet);
        self::assertStringContainsString("@param string \$name The pet's name", $pet);
        self::assertStringContainsString('@param list<string> $tags', $pet);
        self::assertStringContainsString('@param array<string, string>|null $attributes', $pet);
        self::assertStringContainsString('@param list<FixtureToy>|null $toys', $pet);
        self::assertStringContainsString("Union::discriminate(\$data['event'], 'event_type', ['adopted' => FixtureAdoptedEvent::class, 'lost' => FixtureLostEvent::class])", $pet);
    }

    public function testModelsHydrateAndSerialize(): void
    {
        $class = 'OpusDNS\\Client\\Model\\FixturePet';
        $input = [
            'id' => 'pet_01h45ytscbebyvny4gc8cr8ma2',
            'name' => 'Rex',
            'status' => 'pending-adoption',
            'created_on' => '2026-03-01T10:20:30Z',
            'weight' => 12,
            'owner' => ['name' => 'Ada', 'email' => null],
            'attributes' => ['colour' => 'brown'],
            'toys' => [['name' => 'ball']],
            'event' => ['event_type' => 'adopted', 'adopter' => 'Ada'],
        ];

        $pet = $class::fromArray($input);

        self::assertSame('Rex', $pet->name);
        self::assertSame('OpusDNS\\Client\\Enum\\FixturePetStatus', $pet->status::class);
        self::assertSame('pending-adoption', $pet->status->value);
        self::assertInstanceOf(\DateTimeImmutable::class, $pet->createdOn);
        self::assertSame('2026-03-01T10:20:30+00:00', $pet->createdOn->format(DATE_ATOM));
        self::assertSame(12.0, $pet->weight);
        self::assertNull($pet->nickname);
        self::assertSame([], $pet->tags);
        self::assertSame('pet', $pet->kind);
        self::assertSame(301, $pet->redirectCode->value);
        self::assertSame('Ada', $pet->owner->name);
        self::assertNull($pet->owner->email);
        self::assertSame(['colour' => 'brown'], $pet->attributes);
        self::assertSame('ball', $pet->toys[0]->name);
        self::assertSame('OpusDNS\\Client\\Model\\FixtureAdoptedEvent', $pet->event::class);
        self::assertSame('Ada', $pet->event->adopter);

        self::assertSame([
            'id' => 'pet_01h45ytscbebyvny4gc8cr8ma2',
            'name' => 'Rex',
            'status' => 'pending-adoption',
            'created_on' => '2026-03-01T10:20:30Z',
            'weight' => 12.0,
            'tags' => [],
            'owner' => ['name' => 'Ada'],
            'attributes' => ['colour' => 'brown'],
            'kind' => 'pet',
            'redirect_code' => 301,
            'toys' => [['name' => 'ball']],
            'event' => ['adopter' => 'Ada', 'event_type' => 'adopted'],
        ], $pet->toArray());
        self::assertInstanceOf(\JsonSerializable::class, $pet);
        self::assertSame(json_encode($pet->toArray()), json_encode($pet));
    }

    public function testPageModelsImplementPage(): void
    {
        $source = self::source('Model/FixturePetPage.php');
        self::assertStringContainsString('final readonly class FixturePetPage implements ApiModel, Page', $source);
        self::assertStringContainsString('@implements Page<FixturePet>', $source);
        self::assertStringContainsString('@return list<FixturePet>', $source);
        self::assertStringNotContainsString('implements ApiModel, Page', self::source('Model/FixturePet.php'));

        $class = 'OpusDNS\\Client\\Model\\FixturePetPage';
        $page = $class::fromArray([
            'pagination' => ['current_page' => 1, 'has_next_page' => false, 'has_previous_page' => false, 'page_size' => 10, 'total_items' => 1, 'total_pages' => 1],
            'results' => [['id' => 'pet_1', 'name' => 'Rex', 'status' => 'available', 'created_on' => '2026-01-01T00:00:00Z', 'weight' => 1]],
        ]);

        self::assertInstanceOf(Page::class, $page);
        self::assertSame('Rex', $page->results()[0]->name);
        self::assertSame(1, $page->pagination()->totalItems);
        self::assertFalse($page->pagination()->hasNextPage);
    }

    public function testEmptyMapsSerializeAsJsonObjects(): void
    {
        $class = 'OpusDNS\\Client\\Model\\FixturePet';
        $base = ['id' => 'x', 'name' => 'x', 'status' => 'available', 'created_on' => '2026-01-01T00:00:00Z', 'weight' => 1];

        $pet = $class::fromArray($base + ['attributes' => []]);
        self::assertSame([], $pet->attributes);
        self::assertInstanceOf(\stdClass::class, $pet->toArray()['attributes']);
        self::assertStringContainsString('"attributes":{}', json_encode($pet, JSON_THROW_ON_ERROR));
        self::assertSame([], $class::fromArray($pet->toArray())->attributes);

        $pet = $class::fromArray($base + ['attributes' => (object) ['colour' => 'brown']]);
        self::assertSame(['colour' => 'brown'], $pet->attributes);
        self::assertSame(['colour' => 'brown'], $pet->toArray()['attributes']);

        self::assertArrayNotHasKey('attributes', $class::fromArray($base)->toArray());
    }

    public function testDateOnlyFieldsKeepTheirDayInAnyTimezone(): void
    {
        $class = 'OpusDNS\\Client\\Model\\FixturePet';
        $previous = date_default_timezone_get();
        date_default_timezone_set('Pacific/Kiritimati');
        try {
            $pet = $class::fromArray(['id' => 'x', 'name' => 'x', 'status' => 'available', 'created_on' => '2026-01-01T00:00:00Z', 'weight' => 1, 'born_on' => '2026-03-01']);

            self::assertSame('2026-03-01', $pet->bornOn?->format('Y-m-d'));
            self::assertSame('UTC', $pet->bornOn?->getTimezone()->getName());
            self::assertSame('2026-03-01', $pet->toArray()['born_on']);
            self::assertSame('2026-03-01', $class::fromArray($pet->toArray())->toArray()['born_on']);
        } finally {
            date_default_timezone_set($previous);
        }
    }

    public function testUnknownEnumValuesAreKeptAsRawValues(): void
    {
        $class = 'OpusDNS\\Client\\Model\\FixturePet';
        $pet = $class::fromArray(['id' => 'x', 'name' => 'x', 'status' => 'unknown', 'created_on' => '2026-01-01T00:00:00Z', 'weight' => 1, 'redirect_code' => 999]);

        self::assertSame('unknown', $pet->status);
        self::assertSame(999, $pet->redirectCode);
        self::assertSame('unknown', $pet->toArray()['status']);
        self::assertSame(999, $pet->toArray()['redirect_code']);
    }

    public function testUnknownDiscriminatorValuesAreRejected(): void
    {
        $class = 'OpusDNS\\Client\\Model\\FixturePet';
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage("discriminator 'event_type' is \"sold\"");
        $class::fromArray(['id' => 'x', 'name' => 'x', 'status' => 'available', 'created_on' => '2026-01-01T00:00:00Z', 'weight' => 1, 'event' => ['event_type' => 'sold']]);
    }

    public function testServiceClassSignaturesAndCollisionHandling(): void
    {
        $api = self::source('Service/PetService.php');
        self::assertStringContainsString('final class PetService', $api);
        self::assertStringContainsString(
            "public function listPets(\n        ?int \$page = null,\n        FixturePetStatus|string|null \$status = null,\n        ?array \$tagIds = null,\n        ?\\DateTimeImmutable \$bornAfter = null,\n    ): array {",
            $api,
        );
        self::assertStringContainsString('@param int|null $page Server default: 1.', $api);
        self::assertStringContainsString('@param list<string>|null $tagIds Filter by tag. Can be repeated.', $api);
        self::assertStringNotContainsString('@param FixturePetStatus|string|null $status', $api);
        self::assertStringContainsString('@return list<FixturePet>', $api);
        self::assertStringContainsString("query: ['page' => \$page, 'status' => \$status, 'tag_ids' => \$tagIds, 'born_after' => \$bornAfter === null ? null : \$bornAfter->format('Y-m-d')]", $api);
        self::assertStringNotContainsString('use OpusDNS\Client\Serializer;', $api);
        self::assertStringContainsString('return $this->client->hydrate($response, static fn (array $data): array => array_map(static fn (array $item): FixturePet => FixturePet::fromArray($item), $data));', $api);
        self::assertStringContainsString('public function createPet(FixturePetCreate|array $body): ?FixturePet', $api);
        self::assertStringContainsString('return $this->client->hydrate($response, static fn (array $data): FixturePet => FixturePet::fromArray($data), optional: true);', $api);
        self::assertStringContainsString('Required permissions: pets:write', $api);
        self::assertStringContainsString('public function deletePet(string $petId, ?string $ifMatch = null): void', $api);
        self::assertStringContainsString("path: ['pet_id' => \$petId],\n            headers: ['If-Match' => \$ifMatch],", $api);
        self::assertStringContainsString('public function requestAuthBe(string $petId): void', $api);
        self::assertStringContainsString('public function requestAuthCz(string $petId): void', $api);

        self::assertStringContainsString('public function pet(): PetService', self::source('Service/ServiceAccessors.php'));
        self::assertStringContainsString("public const PETS_BY_PET_ID = '/v1/pets/{pet_id}';", self::source('Endpoint.php'));
        self::assertStringContainsString("'/v1/pets' => ['POST' => ['pets:write']]", self::source('Permission.php'));
    }

    public function testRegenerationRemovesStaleFiles(): void
    {
        $stale = self::$out . '/Model/FixtureStale.php';
        file_put_contents($stale, "<?php\n// This file is generated from the OpenAPI specification by bin/generate.\n");
        $foreign = self::$out . '/Model/KeepMe.php';
        file_put_contents($foreign, "<?php\n// hand written\n");

        (new Generator())->generate(__DIR__ . '/../fixtures/fixture-spec.yaml', self::$out);

        self::assertFileDoesNotExist($stale);
        self::assertFileExists($foreign);
        unlink($foreign);
    }
}
