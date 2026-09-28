<?php

namespace App\Services;

use App\Contracts\AnimalServiceInterface;
use Illuminate\Support\Facades\Storage;
use Exception;

class FileAnimalService implements AnimalServiceInterface
{
    private string $filename = 'animals.json';

    private function getAnimals(): array
    {
        // Si el archivo no existe, retorna un array vacío
        if (!Storage::disk('local')->exists($this->filename)) {
            return [];
        }

        $jsonContent = Storage::disk('local')->get($this->filename);
        $data = json_decode($jsonContent, true); 

        return is_array($data) ? $data : [];
    }


    private function saveAnimals(array $animals): void
    {
        $jsonContent = json_encode(array_values($animals), JSON_PRETTY_PRINT);
        
        Storage::disk('local')->put($this->filename, $jsonContent);
    }

    public function all(): array
    {
        return $this->getAnimals();
    }

    public function find(int|string $id): array
    {
        $animals = $this->getAnimals();

        foreach ($animals as $animal) {
            if ($animal['id'] == $id) {
                return $animal;
            }
        }

        throw new Exception("El animal con ID {$id} no fue encontrado.");
    }

    public function create(array $data): array
    {
        $animals = $this->getAnimals();

        $nextId = 1;
        if (count($animals) > 0) {
            $maxId = max(array_column($animals, 'id'));
            $nextId = $maxId + 1;
        }

        $newAnimal = [
            'id' => $nextId,
            'name' => $data['name'],
            'species' => $data['species'],
            'age' => $data['age']
        ];

        $animals[] = $newAnimal;
        $this->saveAnimals($animals);

        return $newAnimal;
    }

    public function delete(int|string $id): bool
    {
        $animals = $this->getAnimals();
        $found = false;

        foreach ($animals as $key => $animal) {
            if ($animal['id'] == $id) {
                unset($animals[$key]);
                $found = true;
                break;
            }
        }

        if (!$found) {
            throw new Exception("El animal con ID {$id} no fue encontrado.");
        }

        $this->saveAnimals($animals);
        return true;
    }

    public function reset(): void
    {
        $seeders = [
            [
                "id" => 1,
                "name" => "Milo",
                "species" => "Perro",
                "age" => 4
            ],
            [
                "id" => 2,
                "name" => "Luna",
                "species" => "Gato",
                "age" => 2
            ]
        ];

        $this->saveAnimals($seeders);
    }

    public function update(int|string $id, array $data): array
    {
        $animals = $this->getAnimals();
        $found = false;
        $updatedAnimal = [];

        foreach ($animals as $key => $animal) {
            if ($animal['id'] == $id) {
                // Actualiza los campos, manteniendo el valor anterior si no se envía uno nuevo
                $animals[$key]['name'] = $data['name'] ?? $animal['name'];
                $animals[$key]['species'] = $data['species'] ?? $animal['species'];
                $animals[$key]['age'] = $data['age'] ?? $animal['age'];
                
                $updatedAnimal = $animals[$key];
                $found = true;
                break;
            }
        }

        if (!$found) {
            throw new Exception("El animal con ID {$id} no fue encontrado.");
        }

        $this->saveAnimals($animals);
        return $updatedAnimal;
    }
}