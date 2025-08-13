<?php

namespace App\Console\Commands;

use App\Models\AutoTagRuleCondition;
use App\Models\AutoTagRuleGroup;
use App\Models\Customer;
use App\Models\CustomerMessage;
use App\Models\CustomerTag;
use App\Models\Form;
use App\Models\FormQuestion;
use App\Models\Message;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Tag;
use App\Models\TagObject;
use Illuminate\Console\Command;
use Faker\Factory as Faker;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class FakeData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fake-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Customer::query()->truncate();
        Form::query()->truncate();
        Question::query()->truncate();
        QuestionOption::query()->truncate();
        FormQuestion::query()->truncate();
        Tag::query()->truncate();
        CustomerTag::query()->truncate();
        $fake = Faker::create();
        Message::query()->truncate();
        CustomerMessage::query()->truncate();
        AutoTagRuleGroup::query()->truncate();
        AutoTagRuleCondition::query()->truncate();
//        dd($fake->title);

        $importCustomer = [];
        $item = [];
        $item['code'] = Str::random(6);
        $item['name'] = 'Thoai Nguyen';
        $item['phone'] = '0356047100';
        $item['email'] = 'nguyenthoai2310@gmail.com';
        $item['app_id'] = '5522212526';
        $item['source'] = 'zalo';
        $importCustomer[] = $item;


        for ($i = 1; $i <= 5; $i++) {
            $item = [];
            $item['code'] = Str::random(6);
            $item['name'] = $fake->name;
            $item['phone'] = $fake->phoneNumber;
            $item['email'] = $fake->email;
            $item['app_id'] = $fake->randomNumber();
            $item['source'] = 'zalo';
            $importCustomer[] = $item;
        }
        Customer::query()->insert($importCustomer);

        return 1;

        $customerIds = Customer::query()->pluck('id')->toArray();

        for ($i = 1; $i <= 2; $i++) {
            $tag = new Tag();
            $tag->name = 'Tag ' . $i;
            $tag->key = 'TAG_' . $i;
            $tag->save();
        }
        $tagIDs = Tag::query()->pluck('id')->toArray();
        foreach ($tagIDs as $tagID) {
            for ($i = 1; $i <= 4; $i++) {
                $tagObject = new CustomerTag();
                $tagObject->customer_id = Arr::random($customerIds);
                $tagObject->tag_id = $tagID;
                $tagObject->save();
            }
        }


        return 1;

        $importForms = [];
        for ($i = 1; $i <= 3; $i++) {
            $item = [];
            $item['code'] = Str::random(6);
            $item['title'] = 'Form ' . $fake->word;
            $item['description'] = $fake->text;
            $item['status'] = 'draft';
            $importForms[] = $item;
        }

        Form::query()->insert($importForms);


        $importQuestions = [];
        $types = ['text', 'radio', 'checkbox'];
        for ($i = 1; $i <= 50; $i++) {
            $item = [];
            $item['code'] = Str::random(6);
            $item['status'] = 1;
            $item['type'] = Arr::random($types);
            $item['title'] = 'Question ' . $fake->word;
            $item['description'] = $fake->text;
            $item['has_other_option'] = Arr::random([-1, 1]);
            $importQuestions[] = $item;
        }

        Question::query()->insert($importQuestions);
        $allQuestions = Question::query()->get();

        $result = collect($allQuestions)->keyBy('id')->toArray();



        $importOptions = [];
        foreach ($allQuestions as $question) {
            for ($j = 1; $j <= 3; $j++) {
                $item = [];
                $item['question_id'] = $question->id;
                $value = $fake->word;
                $item['text'] = 'Answer ' . $value;
                $item['value'] = $value;
                $item['is_other'] = -1;

                $dataQuestion = $result[$question->id];

                if (+$dataQuestion['has_other_option'] === 1 && $j === 3) {
                    $item['is_other'] = 1;
                    $item['text'] = 'Other...';
                    $item['value'] = 'other';
                }
                $item['order'] = $j;
                $importOptions[] = $item;
            }
        }
        QuestionOption::query()->insert($importOptions);


        $allForms = Form::query()->get();

        $questionIds = collect($allQuestions)->pluck('id')->toArray();

        $importFormQuestion = [];

        foreach ($allForms as $form) {
            for ($j = 1; $j <= 5; $j++) {
                $item = [];
                $item['form_id'] = $form->id;
                $item['question_id'] = Arr::random($questionIds);
                $item['required'] = Arr::random([-1, 1]);
                $item['order'] = $j;
                $importFormQuestion[] = $item;
            }
        }
        FormQuestion::query()->insert($importFormQuestion);
    }
}
