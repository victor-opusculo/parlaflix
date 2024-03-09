<?php
namespace VictorOpusculo\Parlaflix\Components\Panels;

use VictorOpusculo\Parlaflix\Components\Data\DateTimeTranslator;
use VictorOpusculo\Parlaflix\Components\Label;
use VictorOpusculo\Parlaflix\Lib\Helpers\Data;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Lesson;
use VictorOpusculo\PComp\Component;

use function VictorOpusculo\PComp\Prelude\component;
use function VictorOpusculo\PComp\Prelude\rawText;
use function VictorOpusculo\PComp\Prelude\tag;
use function VictorOpusculo\PComp\Prelude\text;

class LessonViewer extends Component
{
    protected Lesson $lesson;

    protected function markup(): Component|array|null
    {
        return tag('fieldset', class: 'fieldset', children:
        [
            tag('legend', children: text("Aula nº {$this->lesson->index->unwrapOr(0)}")),

            component(Label::class, label: 'Título', labelBold: true, children: text($this->lesson->title->unwrapOr(''))),
            component(Label::class, label: 'Mais informações', labelBold: true, lineBreak: true, children: rawText(nl2br(Data::hsc($this->lesson->presentation_html->unwrapOr(''))))),
            component(Label::class, label: 'Link da sala (aula ao vivo)', labelBold: true, children: 
                $this->lesson->live_meeting_url->unwrapOr(false) 
                    ? tag('a', class: 'link', href: Data::hscq($this->lesson->live_meeting_url->unwrap()), children: text($this->lesson->live_meeting_url->unwrap()))
                    : text('Nenhum'),
            ),
            component(Label::class, label: 'Data e hora da aula ao vivo', labelBold: true, children:
                $this->lesson->live_meeting_datetime->unwrapOr(false)
                    ? component(DateTimeTranslator::class, isoDateTime: $this->lesson->live_meeting_datetime->unwrap())
                    : text('***')
            ),
            component(Label::class, label: 'Hospedagem de vídeo', labelBold: true, children: text($this->lesson->video_host->unwrapOr(''))),
            component(Label::class, label: 'Vídeo', labelBold: true, lineBreak: true, children:
                component(VideoRenderer::class, videoHost: $this->lesson->video_host->unwrapOr(''), videoCode: $this->lesson->video_url->unwrapOr(''))
            ),
            component(Label::class, label: 'Senha para verificação de presença', labelBold: true, children: text($this->lesson->completion_password->unwrapOr(''))),
            component(Label::class, label: 'Pontos de verificação de presença', labelBold: true, children: text($this->lesson->completion_points->unwrapOr('')))
        ]);
    }
}
