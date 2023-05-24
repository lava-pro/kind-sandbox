<?php

namespace App\Repositories\Tag;

interface TagRepositoryInterface
{
    public function getPaginate();
    public function getById(int $id);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
}
