<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class MainController extends Controller
{
    public function home(): View
    {
        return view('home');
    }

    public function generateExercises(Request $request): View
    {

        $request->validate([
            'check_sum' => 'required_without_all:check_subtraction,check_multiplication,check_division',
            'check_subtraction' => 'required_without_all:check_sum,check_multiplication,check_division',
            'check_multiplication' => 'required_without_all:check_subtraction,check_sum,check_division',
            'check_division' => 'required_without_all:check_subtraction,check_multiplication,check_sum',
            'number_one' => 'required|integer|min:0|max:999|lt:number_two',
            'number_two' => 'required|integer|min:0|max:999',
            'number_exercises' => 'required|integer|min:5|max:50'
        ]);

        $operations = [];
        if($request->check_sum) $operations[] = '+';
        if($request->check_subtraction) $operations[] = '-';
        if($request->check_multiplication) $operations[] = '*';
        if($request->check_division) $operations[] = '/';

        $min = $request->number_one;
        $max = $request->number_two;

        $numberExercises = $request->number_exercises;

        $exercises = [];

        for($i = 1; $i <= $numberExercises; $i++){
            $exercises[] = $this->generateExercise($i, $operations, $min, $max);            
        }

        session(['exercises' => $exercises]);

        return view('operations', ['exercises' => $exercises]);
    }

    public function printExercises()
    {
        if(!session()->has('exercises')) return redirect()->route('home');

        $exercises = session('exercises');

        echo '<pre>';
        echo '<h1>Exercícios de Matemática (' . env('APP_NAME') . ')</h1>';
        echo '<hr>';

        foreach($exercises as $ex){
            echo '<h2><small>' . str_pad($ex['exercise_number'], 2, "0", STR_PAD_LEFT) . ' >> </small> ' . $ex['exercise'] . '</h2>';
        }

        echo '<hr>';
        echo '<small>Soluções</small><br>';
        foreach($exercises as $ex){
            echo '<small>' . str_pad($ex['exercise_number'], 2, "0", STR_PAD_LEFT) . ' >> ' . $ex['result'] . '</small><br>';
        }
    }

    public function exportExercises(){

    }

    private function generateExercise($index, $operations, $min, $max): array
    {
        $operation = $operations[array_rand($operations)];
            $number1 = rand($min, $max);
            $number2 = rand($min, $max);

            $exercise = '';
            $result = '';

            switch($operation){
                case '+':
                    $exercise = "$number1 + $number2 = ";
                    $result = $number1 + $number2;
                    break;

                case '-':
                    $exercise = "$number1 - $number2 = ";
                    $result = $number1 - $number2;
                    break;

                case '*':
                    $exercise = "$number1 X $number2 = ";
                    $result = $number1 * $number2;
                    break;

                case '/':
                    if($number2 == 0) $number2 = 1;

                    $exercise = "$number1 : $number2 = ";
                    $result = $number1 / $number2;
                    break;
            }

            if(is_float($result)) $result = round($result, 2);

            return [
                'operation' => $operation,
                'exercise_number' => $index,
                'exercise' => $exercise,
                'result' => "$exercise $result"
            ];
    }
}
