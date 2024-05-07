<?php

namespace App\Normalizer;

use App\Entity\Task;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;

class TaskNormalizer implements NormalizerInterface {

    /**
     * Normalizes an object into a set of arrays/scalars.
     *
     * @param Task   $object Object to normalize
     * @param string $format Format the normalization result will be encoded as
     * @param array  $context Context options for the normalizer
     *
     * @return array
     */
    public function normalize($object, string $format = null, array $context = []): array {
        if (!$object instanceof Task) {
            throw new InvalidArgumentException('The object to normalize must be an instance of ' . Task::class . '.');
        }

        return [
            'id'          => $object->getId(),
            'description' => $object->getDescription(),
            'deadline'    => $object->getDeadline() ? $object->getDeadline()->format('c') : null, // 'c' is for ISO8601
            'completed'   => $object->isCompleted(),
        ];
    }

    /**
     * Checks whether the given class is supported for normalization by this normalizer.
     *
     * @param mixed  $data Data to normalize.
     * @param string $format The format being (de-)serialized from or into.
     *
     * @return bool
     */
    public function supportsNormalization(mixed $data, string $format = null, array $context = []): bool {
        return $data instanceof Task;
    }

    /**
     * Returns the set of types supported by this normalizer.
     *
     * @return array The list of supported types.
     */
    public function getSupportedTypes(?string $format): array {
      return [Task::class];
    }
}
