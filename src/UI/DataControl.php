<?php

declare(strict_types=1);

namespace Stepapo\Visualization\UI;

use Contributte\ImageStorage\ImageStorage;
use Stepapo\Visualization\Column;
use Stepapo\Visualization\Factory;
use Stepapo\Visualization\View;
use Latte\Essential\RawPhpExtension;
use Nette\Application\UI\Control;
use Nette\Application\UI\Template;
use Nextras\Orm\Collection\ICollection;


abstract class DataControl extends Control
{
    public function render()
    {
        $this->template->imageStorage = $this->getImageStorage();
        $this->template->columns = $this->getColumns();
        $this->template->view = $this->getView();
    }


	protected function createTemplate(?string $class = null): Template
	{
		$template = parent::createTemplate();
		$template->getLatte()->addExtension(new RawPhpExtension);
		return $template;
	}


    abstract public function getMainComponent(): ?MainComponent;


    public function getCollection(): ICollection
    {
        return $this->getMainComponent()->getCollection();
    }


    public function getImageStorage(): ?ImageStorage
    {
        return $this->getMainComponent()->getImageStorage();
    }


    /** @var Column[]|null */
    public function getColumns(): ?array
    {
        return $this->getMainComponent()->getColumns();
    }


    public function getView(): View
    {
        return $this->getMainComponent()->getView();
    }


    public function getFactory(): Factory
    {
        return $this->getMainComponent()->getFactory();
    }


    public function getFilter(): array
    {
        return $this->getMainComponent()->getFilter();
    }
}
