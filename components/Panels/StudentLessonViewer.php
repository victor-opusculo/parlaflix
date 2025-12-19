<?php
namespace VictorOpusculo\Parlaflix\Components\Panels;

use VictorOpusculo\Parlaflix\Components\Data\DateTimeTranslator;
use VictorOpusculo\Parlaflix\Components\Label;
use VictorOpusculo\Parlaflix\Lib\Helpers\Data;
use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Lesson;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\PresenceMethod;
use VictorOpusculo\Parlaflix\Lib\Model\Students\Subscription;
use VictorOpusculo\PComp\Component;

use function VictorOpusculo\PComp\Prelude\component;
use function VictorOpusculo\PComp\Prelude\rawText;
use function VictorOpusculo\PComp\Prelude\scTag;
use function VictorOpusculo\PComp\Prelude\tag;
use function VictorOpusculo\PComp\Prelude\text;

class StudentLessonViewer extends Component
{
    protected Subscription $subscription;
    protected Lesson $lesson;
    protected bool $isPasswordCorrect = false;
    protected bool $isTestCorrect = false;

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
                ? tag('fieldset', class: 'fieldset', children:
                [
                    tag('legend', children: text("Marcar presença/visualização")),
                    component(Label::class, label: 'Pontuação da aula', labelBold: true, children: text($this->lesson->completion_points->unwrapOr(''))),

                    PresenceMethod::satisfiesPassword($this->lesson->presence_method->unwrapOr(''))
                        ? tag('student-lesson-password-submitter', student_id: $_SESSION['user_id'] ?? 0, lesson_id: $this->lesson->id->unwrapOr(0), iscorrect: $this->isPasswordCorrect ? 1 : 0)
                        : null,

                    PresenceMethod::satisfiesTest($this->lesson->presence_method->unwrapOr(''))
                        ? component(Label::class, label: "Questionário", children:
                            !$this->isTestCorrect
                            ? tag('a', 
                                class: 'btn', 
                                href: URLGenerator::generatePageUrl("/student/panel/subscription/fill_test", [ 'lesson_id' => $this->lesson->id->unwrapOr(0), 'back_to_subscription' => $this->subscription->id->unwrapOr(0) ]),
                                children: text("Preencher")
                              )
                            : tag('span', class: 'italic', children:
                            [
                                scTag('img', class: 'inline mr-2', src: URLGenerator::generateFileUrl("/assets/pics/check.png"), width: 32),
                                text("O questionário foi concluído e você foi aprovado!")
                            ])
                        )
                        : null,

                    PresenceMethod::satisfiesAuto($this->lesson->presence_method->unwrapOr(''))
                        ? tag('span', class: 'italic ml-2', children: text("Presença automática, não há necessidade de marcá-la."))
                        : null,
                ])
                : null
            
        ];
    }
}
