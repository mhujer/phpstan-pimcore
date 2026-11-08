<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules;

use Doctrine\DBAL\SQL\Parser;
use Doctrine\DBAL\SQL\Parser\Visitor;
use Mhujer\PHPStan\Pimcore\Rules\ConditionPlaceholderTokenizer;
use PHPUnit\Framework\TestCase;
use Pimcore\Model\DataObject\Listing;

final class ConditionPlaceholderTokenizerTest extends TestCase
{
    public function testIgnoreValueMatchesNativeListing(): void
    {
        $corpus = ['', '?', ':name', "'?'", '"?:name"', "'unterminated ?", "'a\nb?'", "'?' :used", '`?`', '/* :name */', "'a\\\\'?:name'", "'a\\\\\\'?:name'", "'a\n\"?:name\"", "'a\r?:name'", "''?:name'"];
        // Exhaustive short inputs cover adjacent quotes, escaped quotes, unmatched quotes and line breaks.
        $inputs = [''];
        for ($length = 0; $length < 5; $length++) {
            $next = [];
            foreach ($inputs as $prefix) {
                foreach (["'", '"', "\\", '?', ':', "\n", 'a'] as $character) {
                    $next[] = $prefix . $character;
                }
            }

            array_push($corpus, ...$next);
            $inputs = $next;
        }

        foreach (["'", '"', "\\", "\n", "\r", '?', ':', 'a'] as $first) {
            foreach (["'", '"', "\\", "\n", '?'] as $second) {
                $corpus[] = $first . "abc" . $second . "?:name" . $first . $second;
                $corpus[] = $first . $second . $second . "?:name" . $second . $first;
            }
        }

        foreach ($corpus as $sql) {
            $listing = new Listing();
            $listing->addConditionParam($sql, 1);
            $condition = array_values($listing->getConditionParams())[0];
            self::assertIsArray($condition);
            self::assertSame($condition['ignore-value'], (new ConditionPlaceholderTokenizer())->ignoresValue($sql), var_export($sql, true));
        }
    }

    public function testMatchesDbalMysqlParser(): void
    {
        $corpus = [
            'a = ? AND b = :name AND c = :name',
            "a = '?' AND b = \"?:name\" AND c = ?",
            '`?:ignored` = ? AND [?:ignored] = :used',
            "a = ? -- ?:ignored\n AND b = :used",
            '/* ? :ignored */ a = ?',
            'a::text = :used AND b = ?? AND c = ??? AND d = ?',
            "a = 'escaped\\\'?:ignored' AND b = ?",
            'ARRAY[?] = :used AND array[?] = :other',
            'a = :0 AND b = :snake_case AND c = :name9',
            "'unterminated ?",
            'a = \'doubled\'\'?:ignored\' AND b = ?',
            '# ? :name',
        ];
        foreach ($corpus as $sql) {
            $visitor = new class() implements Visitor {
                /** @var list<string> */
                public array $tokens = [];

                public function acceptPositionalParameter(string $sql): void
                {
                    $this->tokens[] = $sql;
                }

                public function acceptNamedParameter(string $sql): void
                {
                    $this->tokens[] = $sql;
                }

                public function acceptOther(string $sql): void
                {
                    unset($sql);
                }
            };
            (new Parser(true))->parse($sql, $visitor);
            self::assertSame($visitor->tokens, (new ConditionPlaceholderTokenizer())->tokenize($sql), $sql);
        }
    }
}
