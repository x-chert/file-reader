<?php

declare(strict_types=1);

namespace Xchert\FileReader\Xml\Middleware;

use Xchert\FileReader\Middleware\MiddlewareInterface;
use Xchert\Schema\Exception\SchemaNotFoundException;
use Xchert\Schema\ObjectSchema;
use Xchert\Schema\SchemaRegistry;
use Xchert\Schema\Type\ListType;
use Xchert\Schema\Type\ObjectType;
use Xchert\Schema\Type\Type;

readonly class ListElementConverter implements MiddlewareInterface
{
    private ObjectSchema $schema;

    /**
     * @throws SchemaNotFoundException
     */
    public function __construct(
        private SchemaRegistry $registry,
        string $schema,
    ) {
        $this->schema = $registry->get($schema);
    }

    public function process(mixed $record): mixed
    {
        return $this->convertObject($record, $this->schema);
    }

    protected function convertObject(mixed $data, ObjectSchema $schema): mixed
    {
        if (!\is_array($data)) {
            return $data;
        }

        foreach ($schema->properties as $name => $property) {
            if (\array_key_exists($name, $data)) {
                $data[$name] = $this->convertValue($data[$name], $property->type);
            }
        }

        return $data;
    }

    protected function convertValue(mixed $value, Type $type): mixed
    {
        if ($type instanceof ListType) {
            // Without the item schema, wrapper elements cannot be distinguished from single objects
            if ($type->itemType instanceof ObjectType && $this->findSchema($type->itemType->schema) === null) {
                return $value;
            }

            return \array_map(
                fn(mixed $item) => $this->convertValue($item, $type->itemType),
                $this->toList($value, $type->itemType)
            );
        }

        if ($type instanceof ObjectType) {
            $schema = $this->findSchema($type->schema);

            return $schema === null ? $value : $this->convertObject($value, $schema);
        }

        return $value;
    }

    protected function toList(mixed $value, Type $itemType): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (!\is_array($value)) {
            return [$value];
        }

        if (\array_is_list($value)) {
            return $value;
        }

        if (\count($value) === 1 && $this->isWrapper(\array_key_first($value), $itemType)) {
            $child = \reset($value);

            return \is_array($child) && \array_is_list($child) ? $child : [$child];
        }

        return [$value];
    }

    /**
     * A single key is a wrapped child unless it is an attribute, text content or a property of the item schema, then
     * the value is a single item.
     */
    protected function isWrapper(int|string $key, Type $itemType): bool
    {
        $key = (string)$key;

        if (\str_starts_with($key, '@') || $key === '#') {
            return false;
        }

        if (!$itemType instanceof ObjectType) {
            return true;
        }

        return $this->findSchema($itemType->schema)?->hasProperty($key) !== true;
    }

    protected function findSchema(string $name): ?ObjectSchema
    {
        try {
            return $this->registry->get($name);
        } catch (SchemaNotFoundException) {
            return null;
        }
    }
}
