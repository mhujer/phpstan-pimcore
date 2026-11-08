<?php

declare(strict_types=1);

namespace Pimcore\Model\DataObject\Objectbrick\Data {
    final class LanguageBrick extends AbstractData
    {
        public const string FIELD_CAPTION = 'caption';

        public function getCaption(?string $language = null): ?string
        {
            return $language;
        }

        public function setCaption(?string $caption, ?string $language = null): static
        {
            $this->setLocalizedValue('caption', $caption, $language);
            return $this;
        }

        private function setLocalizedValue(string $name, ?string $value, ?string $language): void
        {
            unset($name, $value, $language);
        }
    }
}

namespace Pimcore\Model\DataObject\Fieldcollection\Data {
    final class LanguageCollection extends AbstractData
    {
        public const string FIELD_CAPTION = 'caption';

        public function getCaption(?string $language = null): ?string
        {
            return $language;
        }

        public function setCaption(?string $caption, ?string $language = null): static
        {
            unset($caption, $language);
            return $this;
        }
    }
}

namespace {
    use Pimcore\Model\DataObject\Fieldcollection\Data\LanguageCollection;
    use Pimcore\Model\DataObject\Objectbrick\Data\LanguageBrick;

    function languageItems(LanguageBrick $brick, LanguageCollection $collection): void
    {
        $brick->getCaption('fr');
        $brick->setCaption('caption', 'fr');
        $brick->get('caption', 'fr');
        $brick->set('caption', 'caption', 'fr');
        $collection->getCaption('fr');
        $collection->setCaption('caption', 'fr');
        $collection->get('caption', 'fr');
        $collection->set('caption', 'caption', 'fr');
    }
}
