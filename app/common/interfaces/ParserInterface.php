<?php


namespace common\interfaces;


interface ParserInterface
{
    public function parsePosts($decodedBody);

    public function parseAfter($decodedBody);
}