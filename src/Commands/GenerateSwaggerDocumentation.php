<?php namespace Evolco\SwaggerDocs\Commands;

use Illuminate\Console\Command;
use Evolco\SwaggerDocs\Formatter;
use Evolco\SwaggerDocs\Generator;
use Illuminate\Support\Facades\File;
use Illuminate\Contracts\Config\Repository;
use Evolco\SwaggerDocs\Exceptions\ExtensionNotLoaded;
use Evolco\SwaggerDocs\Exceptions\InvalidFormatException;

/**
 * Class GenerateSwaggerDocumentation
 * @package Evolco\SwaggerDocs\Commands
 */
class GenerateSwaggerDocumentation extends Command {

    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'swagger:generate
                            {--format=json : The format of the output, current options are json and yaml}
                            {--f|filter= : Filter to a specific route prefix, such as /api or /v2/api}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Generate Swagger documentation for application';

    /**
     * Config repository instance
     * @var Repository
     */
    protected Repository $configuration;

    /**
     * GenerateSwaggerDocumentation constructor.
     * @param Repository $configuration
     */
    public function __construct(Repository $configuration) {
        $this->configuration = $configuration;
        parent::__construct();
    }

    /**
     * @inheritDoc
     * @throws InvalidFormatException|ExtensionNotLoaded
     */
    public function handle(): void {
        $filter = $this->option('filter') ?: null;
        $format = $this->option('format');

        $documentation = (new Generator($this->configuration, $filter))->generate();
        $formattedDocs = (new Formatter($documentation))->setFormat($format)->format();

        $storagePath = $this->configuration->get('swagger.storage');
        File::isDirectory($storagePath) or File::makeDirectory($storagePath, 0777, true, true);
        $file = implode(DIRECTORY_SEPARATOR, [$storagePath, 'swagger.' . $format]);
        file_put_contents($file, $formattedDocs);
    }
}
