<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace EzSystems\EzPlatformGraphQL\Command;

use Doctrine\DBAL\Schema\SchemaDiff;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use EzSystems\EzPlatformGraphQL\Schema\DynamicSchema\SchemaDiffBuilder;
use EzSystems\EzPlatformGraphQL\Schema\Initializer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HotGraphQLTypesCommand extends Command
{
    protected static $defaultName = 'app:hot-graphql-types';

    /** @var \eZ\Publish\API\Repository\ContentTypeService */
    private $contentTypeService;

    /** @var \EzSystems\EzPlatformGraphQL\Schema\DynamicSchema\SchemaDiffBuilder */
    private $schema;

    /** @var \EzSystems\EzPlatformGraphQL\Schema\Worker[] */
    private $workers;

    public function __construct(ContentTypeService $contentTypeService, SchemaDiffBuilder $schemaBuilder, array $workers)
    {
        parent::__construct();
        $this->contentTypeService = $contentTypeService;
        $this->schema = $schemaBuilder;
        $this->workers = $workers;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $contentType = $this->contentTypeService->loadContentTypeByIdentifier($input->getArgument('content-type'));

        $this->hotLoadContentType($contentType);
    }

    private function hotLoadContentType(ContentType $contentType)
    {
        $contentTypeGroup = $contentType->getContentTypeGroups()[0];

        // prepare iterators ?
        $iterator = [
            ['ContentTypeGroup' => $contentTypeGroup, 'ContentType' => $contentType],
        ];

        foreach ($contentType->getFieldDefinitions() as $fieldDefinition) {
            $iterator[] = ['ContentType' => $contentType, 'FieldDefinition' => $fieldDefinition];
        }

        $this->generateWithIterator($iterator);
        dump($this->schema->getAddedTypes());
    }

    protected function configure()
    {
        $this
            ->addArgument('content-type', InputArgument::REQUIRED)
            ->setDescription("Hot-loads a content type into the GraphQL schema");
    }

    private function generateWithIterator($iterator)
    {
        foreach ($this->workers as $worker) {
            if ($worker instanceof Initializer) {
                $worker->init($this->schema);
            }
        }

        foreach ($iterator as $arguments) {
            foreach ($this->workers as $schemaWorker) {
                if (!$schemaWorker->canWork($this->schema, $arguments)) {
                    continue;
                }
                $schemaWorker->work($this->schema, $arguments);
            }
        }
    }
}
