<?php

namespace App\Repositories\Post;

interface PostRepositoryInterface
{
    public function getPaginate();
    public function getById(int $id);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
    public function search(string $sq);
}
