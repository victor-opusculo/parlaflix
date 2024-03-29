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

class StudentLessonViewer extends Component
{
    protected Lesson $lesson;
    protected bool $isPasswordCorrect = false;

    protected function markup(): Component|array|null
    {
        return
        [
            tag('div', children: rawText(nl2br(Data::hsc($this->lesson->presentation_html->unwrapOr(''))))),

            $this->lesson->live_meeting_url->unwrapOr(false)
                ? component(Label::class, label: 'Link da sala (aula ao vivo)', labelBold: true, children: 
                    tag('a', class: 'link', href: Data::hscq($this->lesson->live_meeting_url->unwrap()), children: text($this->lesson->live_meeting_url->unwrap()))
                  )
                : null,

            $this->lesson->live_meeting_datetime->unwrapOr(false)
                ? component(Label::class, label: 'Data e hora da aula ao vivo', labelBold: true, children:
                    component(DateTimeTranslator::class, isoDateTime: $this->lesson->live_meeting_datetime->unwrap())
                  )
                : null,
            
            component(Label::class, label: 'Vídeo', labelBold: true, lineBreak: true, children:
                $this->lesson->video_url->unwrapOr(false)
                    ? component(VideoRenderer::class, 
                            videoHost: $this->lesson->video_host->unwrapOr(''), 
                            videoCode: $this->lesson->video_url->unwrapOr(''),
                            width: 774,
                            height: 473
                      )
                    : text("Esta aula ainda não tem gravação disponível.")
            ),
            
            $this->lesson->passedLiveMeetingDate()
                ?   tag('fieldset', class: 'fieldset', children:
                    [
                        tag('legend', children: text("Marcar presença/visualização")),
                        component(Label::class, label: 'Pontuação da aula', labelBold: true, children: text($this->lesson->completion_points->unwrapOr(''))),
                        tag('student-lesson-password-submitter', student_id: $_SESSION['user_id'] ?? 0, lesson_id: $this->lesson->id->unwrapOr(0), iscorrect: $this->isPasswordCorrect ? 1 : 0)
                    ])
                : null
            
        ];
    }
}
