<?php

namespace App\Normalizer;

use App\Entity\Task;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class TaskDenormalizer implements DenormalizerInterface {

    public function denormalize($data, string $type, string $format = null, array $context = []): Task {
        $task = new Task();

        if (isset($data['id'])) {
            $task->setId($data['id']);
        }

        if (isset($data['description'])) {
            $task->setDescription($data['description']);
        }

        if (isset($data['deadline']) && $data['deadline'] !== null) {
          $deadline = \DateTime::createFromFormat('Y-m-d', $data['deadline']);
          if ($deadline === false) {
              throw new \Exception("Invalid date format for deadline.");
          }
          $task->setDeadline($deadline);
          } else {
              $task->setDeadline(null);
          }

        if (isset($data['completed'])) {
            $task->setCompleted((bool)$data['completed']);
        }

        return $task;
    }

    public function supportsDenormalization($data, string $type, string $format = null, array $context = []): bool {
        return $type === Task::class;
    }

    /**
     * Returns the set of types supported by this denormalizer.
     *
     * @param string|null $format The format being deserialized from.
     *
     * @return array The list of supported types.
     */
    public function getSupportedTypes(?string $format): array
    {
        return [Task::class];
    }
}
