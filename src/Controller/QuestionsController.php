<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Question;
use App\Entity\Result;
use App\Form\QuestionFormType;
use DateTime;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Validation;

class QuestionsController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ){}

    #[Route('/', name: 'index')]
    public function showIndexPage(): Response
    {
        return $this -> render('index.html.twig', [

            'start_url' => $this->generateUrl('questions', ['action' => 'start'])
        ]);
    }

    #[Route('/questions/{action}', name: 'questions')]
    public function showQuestionsPage(string $action, Request $request): Response
    {
        $session = $request -> getSession();
        $repository = $this -> entityManager -> getRepository(Question::class);

        if($action === 'start')
        {
            $current = 1;
            $session -> set('current', $current);
            $session -> set('results', [
                'processed' => [],
                'passed' => [],
                'failed' => []
            ]);

            $session -> set('total', $repository -> count());
        }
        else
        {
            $results = $session -> get('results');
            //dd($results);
            $current = $session -> get('current');

            if($action === 'next')
            {
                if($current == $session -> get('total'))
                {
                    $record = new Result();
                    $record -> setDate(new DateTime());
                    $record -> setData($results);

                    $this -> entityManager -> persist($record);
                    $this -> entityManager -> flush();

                    return $this -> redirect($this->generateUrl('results'));
                }
                else if($request -> get('item') == $current + 1)
                {
                    $current ++;
                    $session -> set('current', $current);
                }
            }
        }

        $question = $repository -> find($current);

        if($question === null)
            throw $this -> createNotFoundException('404 Page not found');

        $form = $this -> createForm(QuestionFormType::class, null, [
            'answers' => array_flip($question -> getAnswers())
        ]);
    
        $form -> handleRequest($request);
        $your_answer = [];
        $check = null;

        if($form -> isSubmitted() && $form -> isValid())
        {
            $your_answer = $form -> get('answers') -> getData();

            if(count($your_answer) === 0)
                return $this -> redirectToRoute('questions', ['action' => 'check']);

            $check = $question -> checkAnswers($your_answer);

            if(!in_array($question -> getId(), $results['processed']))
            {
                if($check['success'])
                    $results['passed'][] = $question -> getName();
                else
                    $results['failed'][] = $question -> getName();

                $results['processed'][] = $question -> getId();
            }

            $session -> set('results', $results);
        }

        return $this -> render('questions.html.twig', [
            
            'last' => $current == $session -> get('total'),
            'question' => $question,
            'check' => $check,
            'form' => $form,
            'next_question_url' => $this->generateUrl('questions', ['action' => 'next', 'item' => $current + 1])
        ]);
    }
    
    #[Route('/results', name: 'results')]
    public function showResultsPage(Request $request): Response
    {
        $session = $request -> getSession();

        return $this -> render('results.html.twig', [

            'results' => $session -> get('results'),
            'start_url' => $this->generateUrl('questions', ['action' => 'start'])
        ]);
    }

    #[Route('/upload', name: 'upload')]
    public function uploadInitialData(): Response
    {
        $repository = $this -> entityManager -> getRepository(Question::class);
        $path = $this -> getParameter('kernel.project_dir').'/var/question.php';
        $data = include_once $path;
        $count = 0;

        foreach($data as $question => $answers)
        {
            $count ++;

            if(null !== $repository -> findOneBy(['name' => $question]))
                continue;
                
            $record = new Question();
            $record -> setName($question);
            $record -> setAnswers($answers);

            $this -> entityManager -> persist($record);
        }

        $this -> entityManager -> flush();

        return new Response('Processed: '.$count);
    }
}