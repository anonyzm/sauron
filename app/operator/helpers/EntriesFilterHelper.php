<?php


namespace operator\helpers;


class EntriesFilterHelper
{
    static function minusWords(array $entries, array $minusWords): array
    {
        $filteredEntries = [];
        foreach ($entries as $entry) {
            if ($entry->text) {
                foreach ($minusWords as $minusWord) {
                    $re = "/#?({$minusWord}})/mi";
                    if (!preg_match_all($re, $entry->text, $matches, PREG_SET_ORDER, 0)) {
                        $filteredEntries[] = $entry;
                    }
                }
            }
        }
        return $filteredEntries;
    }

    static function minusOld(array $entries, int $minTime): array
    {
        $filteredEntries = [];
        foreach ($entries as $entry) {
            if ($minTime < $entry->created) {
                $filteredEntries[] = $entry;
            }
        }
        return $filteredEntries;
    }
}