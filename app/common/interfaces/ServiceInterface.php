<?php


namespace common\interfaces;


use Psr\Http\Message\ResponseInterface;

interface ServiceInterface
{
    public function loadPosts(string $tag, ?string $after = ''): ?array;
}