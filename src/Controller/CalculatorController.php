<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\NotBlank;

class CalculatorController extends AbstractController
{
    public function getForm(Request $request, Float $result = null, String $error = null)
    {
        $form = $this->getCalculatorForm();
        
        return $this->render('lucky/number.html.twig', [
            'form' => $form->createView(),
            'result' => $result,
            'error' => $error,
        ]);
    }

    public function postForm(Request $request)
    {
        $form = $this->getCalculatorForm();
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            try {
                $result = $this->calculateResult($data);
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }

        } else {
            $error = 'Invalid Data. The value you inserted did not pass the validation.';
        }

        // i couldn't find the function for the internal redirect, and the normal redirect was exposing all the parameters in the url
        return $this->getForm(
            $request,
            $result ?? null,
            $error ?? null
        );

    }

    private function getCalculatorForm()
    {
        $form = $this->createFormBuilder(null, [
            'action' => '/calculator/post',
            'method' => 'POST',
        ]);

        // i wanted to add also the csrf field to the form but for some reason does not like the constraints.

        // i wanted also to explore more precise constriants but i run out of time.
        return $form
            ->add('Operand-1', NumberType::class, [
                'constraints' => new NotBlank()
            ])
            ->add('Operation', ChoiceType::class, [
                'choices'  => [
                    '+' => '+',
                    '-' => '-',
                    '*' => '*',
                    '/' => '/',
                ],
            ])
            ->add('Operand-2', NumberType::class, [
                'constraints' => new NotBlank()
            ])
            ->getForm();
    }

    private function calculateResult($data)
    {
        switch ($data['Operation']) {
            case '+':
                return $data['Operand-1'] + $data['Operand-2'];
            case '-':
                return $data['Operand-1'] - $data['Operand-2'];
            case '*':
                return $data['Operand-1'] * $data['Operand-2'];
            case '/':
                return $data['Operand-1'] / $data['Operand-2'];
                break;

            default:
                // Cannot actually be triggered unless someone modify the dom
                throw new Exception("Invalid Operation", 1);                
        }
    }
}