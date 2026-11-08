<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Rules;

use RuntimeException;

/** Identifies placeholders using DBAL's MySQL SQL parser grammar, without requiring DBAL at runtime. */
final class ConditionPlaceholderTokenizer
{
    /** @return list<string> The positional tokens and colon-prefixed names, in SQL order. */
    public function tokenize(string $sql): array
    {
        $special = ':\?\'"`\\[\\-\\/';
        $quoted = static fn (string $delimiter): string => $delimiter . '((\\\\.)|(?![' . $delimiter . '\\\\]).)*' . $delimiter;
        $other = implode('|', [
            $quoted("'"),
            $quoted('"'),
            '`[^`]*`',
            '(?<!\b(?i:ARRAY))\[(?:[^\]])*\]',
            ':{2,}',
            '--[^\r\n]*',
            '/\*([^*]+|\*+[^/*])*\**\*/',
            '[^' . $special . ']+',
            '[' . $special . ']',
        ]);
        $pattern = '~\\G(?P<named>:[a-zA-Z0-9_]+)|(?P<positional>(?<!\\?)\\?(?!\\?))|(?P<other>' . $other . ')~s';
        $tokens = [];
        $offset = 0;
        while ($offset < strlen($sql)) {
            if (preg_match($pattern, $sql, $matches, 0, $offset) !== 1) {
                throw new RuntimeException('Could not tokenize the listing condition.');
            }

            if ($matches['named'] !== '' || $matches['positional'] !== '') {
                $tokens[] = $matches[0];
            }

            $offset += strlen($matches[0]);
        }

        return $tokens;
    }

    /** Mirrors AbstractListing::addConditionParam(), whose value-ignoring check differs from SQL parsing. */
    public function ignoresValue(string $sql): bool
    {
        $length = strlen($sql);
        for ($offset = 0; $offset < $length; $offset++) {
            $character = $sql[$offset];
            if (($character === "'" || $character === '"') && ($offset === 0 || $sql[$offset - 1] !== "\\")) {
                $end = null;
                for ($cursor = $offset + 1; $cursor < $length && $sql[$cursor] !== "\n"; $cursor++) {
                    if ($sql[$cursor] !== $character) {
                        continue;
                    }

                    $end = $cursor;
                    if ($cursor > $offset + 1 && $sql[$cursor - 1] !== "\\") {
                        break;
                    }
                }

                // An immediately adjacent quote can be consumed as content; the first later unescaped quote ends
                // the span. Pimcore also removes a span ending in an escaped quote when no unescaped end exists.
                // Unmatched quotes and newlines remain visible to its placeholder check.
                if ($end !== null) {
                    $offset = $end;
                    continue;
                }
            }

            if ($character === '?' || $character === ':') {
                return false;
            }
        }

        return true;
    }
}
