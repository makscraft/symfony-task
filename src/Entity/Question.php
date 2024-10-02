<?php

namespace App\Entity;

use App\Repository\QuestionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuestionRepository::class)]
class Question
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    private ?string $name = null;

    #[ORM\Column(type: Types::ARRAY)]
    private array $answers = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getAnswers(): array
    {
        return $this->answers;
    }

    public function setAnswers(array $answers): static
    {
        $this->answers = $answers;

        return $this;
    }

    public function checkAnswers(array $your_answer): array
    {
        $summ = 0;
        $correct = [];
        
        $final = [
            'success' => true,
            'question' => $this -> name,
            'answers' => []
        ];

        

        foreach(preg_split('/\s*\+\s*/', $this -> name) as $value)
            $summ += (int) $value;

        foreach($this -> answers as $key => $answer)
        {
            $next = 0;

            foreach(preg_split('/\s*\+\s*/', strval($answer)) as $value)
                $next += (int) $value;

            if($next === $summ)
                $correct[] = $key;
        }        

        foreach($your_answer as $value)
            if(!in_array($value, $correct))
            {
                $final['success'] = false;

                foreach($correct as $key => $value)
                    $correct[$key] = $value + 1;

                $final['answers'] = [[]];

                foreach($correct as $value)
                    foreach($final['answers'] as $combination)
                        array_push($final['answers'], array_merge([$value], $combination));

                foreach($final['answers'] as $key => $one)
                    if($one === [])
                        unset($final['answers'][$key]);
                    else
                    {
                        $final['answers'][$key] = implode(' И ', $one);

                        if(count($one) > 1)
                            $final['answers'][$key] = '('.$final['answers'][$key].')';
                    }

                usort($final['answers'], function($a, $b)
                {
                    return strlen($a) > strlen($b);
                });

                $final['answers'] = implode(' ИЛИ ', $final['answers']);

                break;
            }

        return $final;
    }
}
