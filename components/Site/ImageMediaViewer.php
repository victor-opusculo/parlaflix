<?php
namespace VictorOpusculo\Parlaflix\Components\Site;

use VictorOpusculo\Parlaflix\Lib\Helpers\Data;
use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;
use VictorOpusculo\Parlaflix\Lib\Model\Media\Media;
use VictorOpusculo\PComp\Component;

use function VictorOpusculo\PComp\Prelude\scTag;
use function VictorOpusculo\PComp\Prelude\tag;
use function VictorOpusculo\PComp\Prelude\text;

class ImageMediaViewer extends Component
{
    protected ?Media $media = null;
    protected ?int $forceWidth = null;
    protected ?int $forceHeight = null;

    protected function markup(): Component|array|null
    {
        $forcedWidth = $this->forceWidth ? [ 'width' => $this->forceWidth ] : [];
        $forcedHeight = $this->forceHeight ? [ 'height' => $this->forceHeight ] : [];
        $forcedSizes = [ ...$forcedWidth, ...$forcedHeight ];

        return isset($this->media) ? 
            scTag('img', 
                ...$forcedSizes,
                src: URLGenerator::generateFileUrl($this->media->fileNameFromBaseDir()), 
                title: Data::hscq($this->media->description->unwrapOr('')),
                alt: Data::hscq($this->media->description->unwrapOr(''))
            )
        :
            text('Sem imagem');
    }
}