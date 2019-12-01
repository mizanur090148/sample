<?php

namespace App\Repository\Api\Provider;

use Illuminate\Http\Request;
use App\Repository\Api\ApiResponseHandler;
use App\Repository\Api\ApiCommonProcessHandler;
use App\Model\Classes\Instruction;
use App\Model\Classes\InstructionStudentPivot;
use App\Model\Classes\Classes;
use App\Model\Assessment\AssessmentType;
use App\Model\Assessment\Assessment;
use App\Model\Assessment\AssessmentUserPivot;
use App\Model\Assessment\AssessmentResult;
use App\Model\Assessment\AssessmentOption;
use App\Model\Assessment\AssessmentResultAnswerPivot;
use App\Model\Classes\ClassTeacherPivot;
use App\Model\Assessment\Revision;
use App\Model\Assessment\TopicContentCustom;
use App\Model\Sentinel\User;
use Kris\LaravelFormBuilder\FormBuilder;
use App\Model\Organization\Organization;
use Underscore\Types\Arrays;
use App\Model\Assessment\TopicContent;
use App\Model\Assessment\AssessmnetCustom;
use App\Model\Assessment\AssessmnetOptionCustom;
use App\Model\Classes\ClassStudentsPivot;
use App\Model\Classes\Section;
use App\Model\Badge\Badge;
use App\Model\Badge\ClassBadge;
use App\Model\Badge\StudentBadge;
use App\Repository\Facades\SampleApi;
use Carbon\Carbon;
use Uuid;
use DB;

/**
 * Instruction API provider
 */
class ApiAssessmentProvider
{
    protected $apiResposeHandler;

    protected $apiCommonProcessHandler;

    function __construct(FormBuilder $formBuilder)
    {
        /**
         * Load API Response handler class
         */
        $this->apiResposeHandler = new ApiResponseHandler();
        $this->apiCommonProcessHandler = new ApiCommonProcessHandler($formBuilder);
    }


    function isScheduled($schedule_date)
    {

        if (!$schedule_date) {

            return false;
        }

        $eventTime = Carbon::parse($schedule_date);
        $mytime = Carbon::now();

        return $mytime->diffInHours($eventTime, false) <= 0 ? true : false;

    }

    /**
     * Get Instruction data
     *
     * @param Request $request
     *
     * @return Array
     */
    public function instructionList(Request $request)
    {
        if ($request->get('section_id')) {
            //Merge where clause
            $request = $this->apiCommonProcessHandler->requestWhereMerge($request, ['section_id' => $request->get('section_id')]);

        } else {
            return $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['error'],
                'Section Id is required for Inistruction List.'
            );
        }
        $userId = $request->get('user_id');
        $isStudent = ($request->get('quitch_user') && $request->get('quitch_user') == STUDENT) ? true : false;
        $isFetchQuestion = ($request->get('fetch_question')) ? true : false;

        $modelData = $this->apiCommonProcessHandler->getModelListByModel(Instruction::class, $request);

        if ($modelData['status'] == $this->apiResposeHandler->responseStatus['success']) {
            foreach ($modelData['content'] as $items) {
                if ($isFetchQuestion) {
                    $question = Instruction::find($items['id'])->assessment()->orderBy('order_dnd')->get();
                    $items['question'] = $question;
                    $items['question_count'] = count($question);
                }               

                if ($request->get('resource_tab')) {

                    $topic_contents = TopicContent::where('content_mapping_id', $items['id'])
                        ->first(['extension', 'schedule_date', 'send_status']);                   

                    $items['extension'] = $topic_contents->extension;

                    $items['scheduled'] = $this->isScheduled($topic_contents->schedule_date);

                    $items['scheduled_date'] = $topic_contents->schedule_date;

                    $items['send_status'] = $topic_contents->send_status;
                }

                $items['instructions'] = Instruction::find($items['id'])->instructions()->orderBy('order_dnd')->get();

                if ($isStudent)
                    $items['instruction_view'] = Instruction::instruction_view($items['id'], $userId);

                if (!$isStudent)
                    $items['instruction_views'] = Instruction::find($items['id'])->instruction_views;
            }

            if ($request->get('resource_tab')) {

                $content = $modelData['content'];

                $content = $content->filter(function ($value, $key) {
                    return $value->send_status == 1;
                })->values();
            }
        }

        return $modelData;
    }

    /**
     * Save Instruction data
     *
     * @param Request $request
     *
     * @return Array
     */
    public function saveInstructionData(Request $request)
    {
        $arrFieldsToSave = ['title', 'instruction', 'type', 'section_id', 'parent_id', 'point', 'url', 'file_location'];

        //Set default point value from class if not provided for instruction
        if (!$request->get('point')) {

            $instructionDefaultPoint = DB::table('classes')->leftJoin('sections', 'sections.class_id', 'classes.id')->where('sections.id', $request->get('section_id'))->value('default_instruction_point');

            $request['point'] = $instructionDefaultPoint;
        }

        $modelData = $this->apiCommonProcessHandler->saveModelDataByModel($request, 'instructions', Instruction::class, $arrFieldsToSave);

        if ($modelData['status'] == $this->apiResposeHandler->responseStatus['success']) {          

            $arrFieldsToSave = ['topic_id', 'content_mapping_id'];
            $request->request->add(['topic_id' => $modelData['content']['section_id']]);
            $request->request->add(['content_mapping_id' => $modelData['content']['id']]);

            if ($request->get('extension')) {

                $arrFieldsToSave[] = 'extension';
            }

            if ($request->has('child_parent_id')) {

                $arrFieldsToSave[] = 'parent_id';
                $request->request->add(['parent_id' => $request->child_parent_id]);
            }

            $data = TopicContent::where('topic_id', $request->get('topic_id'))->orderBy('order_dnd', 'DESC')->first();

            if ($data) {

                $arrFieldsToSave[] = 'order_dnd';

                $request->request->add(['order_dnd' => $data->order_dnd + 1]);  
            }           

            $this->apiCommonProcessHandler->saveModelDataByModel($request, 'topic_content', TopicContent::class, $arrFieldsToSave);

            $modelData['content'] = TopicContent::with('instruction')->where('content_mapping_id', $modelData['content']->id)->first();          
        }

        return $modelData;
    }

    /**
     * Delete Instruction data
     *
     * @param Request $request
     *
     * @return Array
     */
    public function deleteInstruction(Request $request)
    {
        TopicContent::where('content_mapping_id', $request->id)->delete();
        return $this->apiCommonProcessHandler->deleteModelDataById($request, Instruction::class);
    }

    /**
     * Get AssessmentType data
     *
     * @param Request $request
     *
     * @return Array
     */
    public function assessmentTypeList(Request $request)
    {
        $modelData = $this->apiCommonProcessHandler->getModelListByModel(AssessmentType::class, $request);

        return $modelData;
    }

    /**
     * Save AssessmentType data
     *
     * @param Request $request
     *
     * @return Array
     */
    public function saveAssessmentTypeData(Request $request)
    {
        $arrFieldsToSave = ['name'];
        $modelData = $this->apiCommonProcessHandler->saveModelDataByModel($request, 'assessments_types', AssessmentType::class, $arrFieldsToSave);

        return $modelData;
    }

    /**
     * Delete AssessmentType data
     *
     * @param Request $request
     *
     * @return Array
     */
    public function deleteAssessmentType(Request $request)
    {
        return $this->apiCommonProcessHandler->deleteModelDataById($request, AssessmentType::class);
    }



    /**
     * Get Assessment data
     *
     * @param Request $request
     *
     * @return Array
     */   

    public function assessmentList(Request $request)
    {
        if ($request->get('section_id')) {
            $request = $this->apiCommonProcessHandler->requestWhereMerge($request, ['section_id' => $request->get('section_id')]);

        } else {
            return $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['error'],
                'Section Id is required for Assessment List.'
            );
        }

        if ($request->get('id')) {
            $request = $this->apiCommonProcessHandler->requestWhereMerge($request, ['id' => $request->get('id')]);
        }

        $modelData = $this->apiCommonProcessHandler->getModelListByModel(Assessment::class, $request);
        if ($modelData['status'] == $this->apiResposeHandler->responseStatus['success']) {

            if ($request->get('revision_tab')) {

                $data = DB::table('topic_content')
                    ->join('assessments',
                        'topic_content.content_mapping_id',
                        '=', 'assessments.id')
                    ->leftJoin('assessments_types', 'assessments.assessment_type_id', '=', 'assessments_types.id')
                    ->leftJoin('assesment_result', function ($join) use ($request) {

                        $join->on('topic_content.content_mapping_id', '=', 'assesment_result.assessment_id')
                            ->where('assesment_result.student_id', $request->get('user_id'));
                        //->where('organizations.deleted_at', '=',NULL );
                    })
                    ->select('assessments.*', 'topic_content.schedule_date',
                        'topic_content.send_status',
                        'assesment_result.point as scorepoint', 'assesment_result.assessment_status as assessmentstatus', 'assessments_types.name as assessment_type_name')
                    ->where('topic_content.topic_id', $request->get('section_id'))
                    ->where('topic_content.content_type', 'question')
                    ->orderBy('topic_content.order_dnd', 'ASC')
                    ->get();

                foreach ($data as $key => $items) {

                    $data[$key]->scheduled = $this->isScheduled($items->schedule_date);

                    $stats = [
                        'answered' => 0,
                        'correct' => 0,
                        'incorrect' => 0,
                        'average_time' => 0
                    ];

                    $data[$key]->stats = $stats;
                }

                $data = $data->filter(function ($value, $key) {

                    return $value->send_status == 1;

                })->values();


                $modelData['content'] = $data;

                $carbon_class = Carbon::class;
                $modelData['content'] = $modelData['content']->filter(function ($value, $key) use ($carbon_class) {

                    //return  $value->scheduled; //old
                    return $value->send_status > 0;
                })->values();
            }

            if ($request->get('id')) {
                foreach ($modelData['content'] as $key => $items) {

                    $assessment_detail = Assessment::with('assessment_option')->find($modelData['content'][$key]['id']);

                    $modelData['content'][$key]['assessment_option'] = $assessment_detail->assessment_option;
                }

            }

            if ($request->get('question_id')) {

                $question_id = $request->get('question_id');

                $data = AssessmnetCustom::with('assessment_option', 'assessment_type')->find($question_id);
                $modelData['content'][] = $data;
            }

        }

        return $modelData;
    }

    /**
     * Get Question list pagination
     * Current User Id
     * @param Request $request
     *
     * @return Array
     */
    public function getQuestionList(Request $request)
    {
        $section_id = "";
        $total_discussion = 0;

        if ($request->get('section_id')) {

            $section_id = $request->get('section_id');

        } else {
            return $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['error'],
                'Section Id is required for Assessment List'
            );
        }

        try {
            $model = new Assessment();

            /*send first 5 comment that has no child*/
            $skip = 0;
            $take = 1;

            $sortOrder = 'title';
            $sortDirection = 'ASC'; 

            if ($request->get('from')) {
                $skip = $request->get('from');
            }

            if ($request->get('to')) {
                $take = $request->get('to');
            }

            //$skip = $skip -1;
            if ($skip < 0) $skip = 0;


            $modelData = $this->getQuestionData($section_id, $request, $take, $skip);


        } catch (\Exception $ex) {
            //otherwise return error
            return $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['error'],
                $ex->getMessage()
            );
        }
        if ($modelData) {
            //Return faculty object
            $modelData = $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['success'],
                $modelData
            );

            $modelData['total_question'] = $total_discussion;

            $modelData['from'] = (int)$request->get('from');
            $modelData['to'] = (int)$take;

            return $modelData;
        } else {
            //otherwise return error
            return $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['error'],
                $this->apiResposeHandler->errorListMessage()
            );
        }
    }

    /**
     * @param $section_id
     * @param $request
     * @param int $take
     * @param int $skip
     * @param bool $id_list
     * @param bool $revise
     * @return mixed
     * get question data
     */
    public function getQuestionData($section_id, $request, $take = 1, $skip = 0, $id_list = false, $revise = false)
    {
        $sortOrder = 'title';
        $sortDirection = 'ASC';
        $whereQuery = [
            'section_id' => $section_id
        ];

        $total_discussion = DB::table('assessments')
            ->where($whereQuery)
            ->count();

        if ($request->get('question_id') && !$revise) {

            $whereQuery = [
                'section_id' => $section_id,
                'id' => $request->get('question_id')
            ];
        }

        if ($id_list) {

            $id_list = json_decode($id_list);

            $modelData1 = [];

            if (count($id_list) > 0) {

                $modelData1 = DB::table('assessments')
                    ->where($whereQuery)
                    ->whereIn('id', $id_list)
                    ->orderBy($sortOrder, $sortDirection)
                    ->get();
            }

            $take = $take - count($modelData1);
            $modelData2 = DB::table('assessments')
                ->where($whereQuery)
                ->whereNotIn('id', $id_list)
                ->take($take)
                ->skip($skip)
                ->orderBy($sortOrder, $sortDirection)
                ->get();          

            if (count($id_list) > 0) {

                $modelData = $modelData1->merge($modelData2);
            } else {

                $modelData = $modelData2;
            }

        } else {

            $modelData = DB::table('assessments')
                ->where($whereQuery)
                ->take($take)
                ->skip($skip)
                ->orderBy($sortOrder, $sortDirection)
                ->get();
        }

        foreach ($modelData as $ind => $item) {

            $data = Assessment::with('assessment_option', 'assessment_type')->find($item->id);       

            $modelData[$ind]->assessment_option = $data->assessment_option;
            $modelData[$ind]->assessment_type = $data->assessment_type;
        }

        return $modelData;
    }

    /**
     * Save Assessment data
     *
     * @param Request $request
     *
     * @return Array
     */
    public function saveAssessmentData(Request $request)
    {
        if ($request->has('quitch_user')) {
            $update = DB::table('assessments')->where('id', $request->get('id'))->update(['title' => $request->get('title')]);
            return $update;
        }
        $arrFieldsToSave = ['title', 'instruction_id', 'section_id', 'assessment_type_id', 'point', 'time_duration', 'can_skip', 'question_hints', 'explanation'];
        //DB switch if Domain

        //Set default point value from class if not provided for assessment

        if ($request->get('no_of_attempts')) {
            $arrFieldsToSave[] = 'no_of_attempts';
        }
        if ($request->get('img_question')) {
            $arrFieldsToSave[] = 'img_question';
        }

        if ($request->get('difficulty')) {
            $arrFieldsToSave[] = 'difficulty';
        }


        if (!$request->get('point')) {
            $assessmentDefaultPoint = DB::table('classes')->leftJoin('sections', 'sections.class_id', 'classes.id')->where('sections.id', $request->get('section_id'))->value('default_assessment_point');
            $request['point'] = $assessmentDefaultPoint;
        }
        //Set default no of attempts from class if not provided for assessment


        $assessmentDefaultAttempt = DB::table('classes')->leftJoin('sections', 'sections.class_id', 'classes.id')->where('sections.id', $request->get('section_id'))->value('default_no_of_attempts');
      
        if ($assessmentDefaultAttempt && $request->get('no_of_attempts') == 1) {

            $request->merge(['no_of_attempts' => $assessmentDefaultAttempt]);

        }

        $modelData = $this->apiCommonProcessHandler->saveModelDataByModel($request, 'assessments', Assessment::class, $arrFieldsToSave);


        if ($modelData['status'] == $this->apiResposeHandler->responseStatus['success']) {          

            $arrFieldsToSave = ['topic_id', 'content_mapping_id', 'content_type'];

            $request->request->add(['topic_id' => $modelData['content']['section_id']]);

            $request->request->add(['content_type' => 'question']);

            $request->request->add(['content_mapping_id' => $modelData['content']['id']]);

            if ($request->has('child_parent_id')) {

                $arrFieldsToSave[] = 'parent_id';
                $request->request->add(['parent_id' => $request->child_parent_id]);

            }

            $data = TopicContent::where('topic_id', $request->get('topic_id'))->orderBy('order_dnd', 'DESC')->first();

            if ($data) {

                $arrFieldsToSave[] = 'order_dnd';
                $request->request->add(['order_dnd' => $data->order_dnd + 1]);              
            }

            $this->apiCommonProcessHandler->saveModelDataByModel($request, 'topic_content', TopicContent::class, $arrFieldsToSave);
            $modelData['content'] = TopicContent::with('question')->where('content_mapping_id', $modelData['content']->id)->first();

        }

        return $modelData;
    }

    /**
     * Delete Assessment data
     *
     * @param Request $request
     *
     * @return Array
     */
    public function deleteAssessment(Request $request)
    {
        \DB::table('topic_content')->where('content_mapping_id', $request->id)->delete();
        return $this->apiCommonProcessHandler->deleteModelDataById($request, Assessment::class);
    }


    /**
     * Clear assessment-user pivot table
     *
     * @param String $type
     * @param String $id
     */
    private function clearAssessmentOptionResultById($type, $id)
    {
        AssessmentResultAnswerPivot::where($type, $id)->delete();
    }

    /**
     * Add assessment-option/assessment-result pivot data
     *
     * @param String $assessmentId
     * @param String $userId
     */
    private function addAssessmentOptionResult($assessmentresultId, $assessmentOptionId, $no_of_attempts)
    {
        AssessmentResultAnswerPivot::create(['assesment_result_id' => $assessmentresultId, 'assesment_option_id' => $assessmentOptionId, 'no_of_attempts' => $no_of_attempts]);
    }

    /**
     * Add assessment-option/assessment-result pivot data
     *
     * @param String $assessmentId
     * @param String $userId
     */
    private function addAssessmentOptionResultByAnswertext(
        $assessmentresultId,
        $answertext,
        $no_of_attempts,
        $teacher_comment = ""
    )
    {
        AssessmentResultAnswerPivot::create(['assesment_result_id' => $assessmentresultId,
            'answer_text' => $answertext,
            'no_of_attempts' => $no_of_attempts,
            'teacher_comment' => $teacher_comment
        ]);
    }


    /**
     * Get AssessmentResult data
     *
     * @param Request $request
     *
     * @return Array
     */
    public function assessmentResultList(Request $request)
    {

        if ($request->get('student_id') || $request->get('assessment_id')) {


            $request_array = $request->all();

            if ($request->get('student_id')) {

                $request_array['where'] = ['student_id' => $request->get('student_id')];
            }

            if ($request->get('assessment_id')) {

                $request_array['where'] = ['assessment_id' => $request->get('assessment_id')];
            }

            $request->replace($request_array);

        } else {
            return $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['error'],
                'Either Student or Assessment Id is required for Assessment Result List'
            );
        }

        $modelData = $this->apiCommonProcessHandler->getModelListByModel(AssessmentResult::class, $request);


        if ($modelData['status'] == $this->apiResposeHandler->responseStatus['success']) {
            foreach ($modelData['content'] as $items) {

                //Send users
                $modelData['content']['options'] = AssessmentResult::find($items['id'])->assessment_answer_result;

                $modelData['content']['answer_text'] = AssessmentResultAnswerPivot::where(
                    'assesment_result_id', $items['id'])->where('answer_text', '<>', '')->get();
            }
        }

        return $modelData;
    }

    /**
     * this function return badge id
     */
    public function getBadgeId($title)
    {
        $data = null;
        $badge = DB::table('badges')->where('title', $title)->first();
        if (isset($badge->id)) {
            $data = $badge->id;
        }
        return $data;
    }

    /**
     * this function return badge id
     */
    public function achievedBadged($title, $class_id, $student_id)
    {
        $batch_id = $this->getBadgeId($title); // get badge id
        $ifBadgeEnableInClass = DB::table('class_badge')
            ->where(['class_id' => $class_id, 'badge_id' => $batch_id])
            ->count();

        if ($ifBadgeEnableInClass > 0) {

            $ifExistSameBadge = DB::table('class_student_badges')
                ->where(['class_id' => $class_id, 'student_id' => $student_id, 'badge_id' => $batch_id])
                ->count();

            if ($ifExistSameBadge == 0) {

                $class_student_badges_input = [
                    'id' => Uuid::generate()->string,
                    'class_id' => $class_id,
                    'student_id' => $student_id,
                    'badge_id' => $batch_id,
                ];

                $student_badge = StudentBadge::create($class_student_badges_input);
                if ($student_badge) {
                    return Badge::where('title', $title)->first(['badges.title', 'badges.earning_message', 'badges.image', 'badges.details']);
                }
            }
        }
    }

    /**
     * @param $title
     * @param $class_id
     * @param $student_id
     * @return mixed
     * get streak badge
     */
    public function achievedStreakBadged($title, $class_id, $student_id)
    {
        if ($title == StreakBadge['3']) {

            $ifStreackBadgeExist = DB::table('class_student_badges')
                ->where(['class_id' => $class_id, 'student_id' => $student_id, 'badge_id' => $this->getBadgeId(StreakBadge['5'])])
                //->whereNotIn('badge_id', [ $this->getBadgeId(StreakBadge['5']), $this->getBadgeId(StreakBadge['10']), $this->getBadgeId(StreakBadge['20']) ])
                ->count();

            if ($ifStreackBadgeExist == 0) {
                return $this->achievedBadged($title, $class_id, $student_id);
            }


        } elseif ($title == StreakBadge['5']) {

            $ifStreackBadgeExist = DB::table('class_student_badges')
                ->where(['class_id' => $class_id, 'student_id' => $student_id, 'badge_id' => $this->getBadgeId(StreakBadge['10'])])
                //->whereNotIn('badge_id', [ $this->getBadgeId(StreakBadge['10']), $this->getBadgeId(StreakBadge['20'] )])
                ->count();

            if ($ifStreackBadgeExist == 0) {
                return $this->achievedBadged($title, $class_id, $student_id);
            }

        } elseif ($title == StreakBadge['10']) {

            $ifStreackBadgeExist = DB::table('class_student_badges')
                ->where(['class_id' => $class_id, 'student_id' => $student_id, 'badge_id' => $this->getBadgeId(StreakBadge['20'])])
                //->whereNotIn('badge_id', [ $this->getBadgeId($this->getBadgeId(StreakBadge['20'])) ])
                ->count();

            if ($ifStreackBadgeExist == 0) {
                return $this->achievedBadged($title, $class_id, $student_id);
            }

        } elseif ($title == StreakBadge['20']) {

            return $this->achievedBadged($title, $class_id, $student_id);
        }

    }

    /**
     * Save AssessmentResult data
     *
     * @param Request $request
     *
     * @return Array
     */
    public function saveAssessmentResultData(Request $request, $teacher_attempt = false)
    {
        /* mandatory assessment_id from api input */
        if (!$request->get('assessment_id')) {
            return $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['error'],
                $this->apiResposeHandler->makeMessage('Assessment Id Required.')
            );
        }

        $earned_batch_array = [];
        /* mandatory fields and will be added gradually */
        $arrFieldsToSave = ['student_id', 'assessment_id', 'point'];

        $assessment_id = $request->get('assessment_id');
        $assessment = Assessment::with('assessment_option', 'assessment_type')->find($assessment_id);

        if ($assessment) {
            $aseesment_attempt = $assessment->no_of_attempts;
            $assessment_type = $assessment->assessment_type->name;

            if ($assessment_type == 'True / False') {
                $assessment_type = "mcq";
            }
        }

        if (($assessment_type == "free_text" || $assessment_type == "short_free_text") && !$teacher_attempt && !$request->get('answer_text')) {
            return $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['error'],
                $this->apiResposeHandler->makeMessage('Must provide Answer Text For Free text Question')
            );
        }

        if ($assessment_type == "draganddrop"
            && !$teacher_attempt && !$request->get('dragdrop_answer_order')
            && !$request->get('can_skip')
        ) {

            return $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['error'],
                $this->apiResposeHandler->makeMessage('Must provide Drag And Drop Sequence')
            );
        }

        //draganddrop
        /* can skip will allow this attempt but a penalty will be made */
        if (($assessment_type == "mcq" || $assessment_type == "draganddrop") && !$teacher_attempt

            && !$request->get('can_skip')
            && !$request->get('assesment_option_id')
            && !$request->get('can_skip')

        ) {

            return $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['error'],
                $this->apiResposeHandler->makeMessage('Must provide Atleast One Option For Multiple Choice Question')
            );
        }

        /*
            if any student choose all answer or select options more than correct answer
        */
        $prevent_false_attempt_with_penalty = false;

        /* attempt calcualtion */

        $no_of_attempts = 1;
        $max_available_point = $assessment->point;

        if ($request->get('can_skip')) {

            $request->request->add(['no_of_attempts' => $no_of_attempts]);
            $arrFieldsToSave[] = 'no_of_attempts';
        }

        /* check if this has previous entry */
        $student_id = app(ApiClassProvider::class)->getprofileId($request, $request->get('student_id'))->profile_id;
        $class_id = DB::table('sections')->where('id', $request->section_id)->first()->class_id;  

        $request->merge(['student_id' => $student_id]);
        $isExist = AssessmentResult::with('assessment')->where('student_id', $request->get('student_id'))->where('assessment_id', $request->get('assessment_id'))->first();

        if ($isExist) {

            if ($isExist->assessment_status == "passed" && !$teacher_attempt) {

                $question_option = [];
                foreach ($assessment->assessment_option as $item) {

                    if ($item->is_correct == 1) {
                        $question_option[$item->id] = $item->is_correct;
                    }
                }

                $evaluation_status = ["assessment_status" =>
                    $this->question_evaluation($assessment_type, $assessment, $request)];


                $question_option = $this->question_option($assessment);

                $evaluation_status['correctanswer'] = "Correct answer is - " . implode(',', $question_option);


                return $this->apiResposeHandler->returnResponse(
                    $this->apiResposeHandler->responseStatus['success'],                   

                    $evaluation_status              
                );              
            }


            /* FREE TEXT WILL ONLY BE ANSWERED ONCE */

            if (($assessment_type == "free_text" || $assessment_type == "short_free_text") && !$teacher_attempt) {

                return $this->apiResposeHandler->returnResponse(
                    $this->apiResposeHandler->responseStatus['error'],
                    $this->apiResposeHandler->makeMessage('Question Already Evaluated')
                );

            }

            $no_of_attempts = $isExist->no_of_attempts;
            /* student attempt */
            if (($assessment_type == "mcq" || $assessment_type == "draganddrop") && !$teacher_attempt) {
                $no_of_attempts = $no_of_attempts + 1;
            }

            $max_available_point = $isExist->max_available_point;

            $id = $isExist->id;
            $request->request->add(['id' => $id]);
            $request->request->add(['no_of_attempts' => $no_of_attempts]);
            $arrFieldsToSave[] = 'no_of_attempts';
        }

        /* teacher attemp must provide assessment status
            because he will evaluate it manually and will give pass /fail manually
        */
        if ($teacher_attempt) {

            /* making assessment_status required field */
            if (!$request->get('assessment_status')) {

                return $this->apiResposeHandler->returnResponse(
                    $this->apiResposeHandler->responseStatus['error'],
                    $this->apiResposeHandler->makeMessage('Teacher Must provide Assessment Status')
                );
            }

            $assessment_status = $request->get('assessment_status');
            /* making point required field */
            if (!$request->get('point')) {

                return $this->apiResposeHandler->returnResponse(
                    $this->apiResposeHandler->responseStatus['error'],
                    $this->apiResposeHandler->makeMessage('Teacher Must provide Point for evaluation')
                );
            }
        }

        /* this is to prevent */
        $from_challange = false;

        if (($assessment_type == "mcq" || $assessment_type == "draganddrop") && !$teacher_attempt && $no_of_attempts > $aseesment_attempt) {

            if ($request->get('challange')) {

                $from_challange = true;

            } else {

                $question_option = [];             
                if ($assessment_type == "mcq") {

                    /* all assessment options */                  

                    foreach ($assessment->assessment_option as $item) {

                        if ($item->is_correct == 1) {
                            $question_option[] = $item->title;
                        }
                    }
                }

                if ($assessment_type == "draganddrop") {

                    $answer_order = $request->get('dragdrop_answer_order');
                    $answer_order = (array)json_decode($answer_order);

                    $db_answer_order = json_decode($assessment->dragdrop_answer_order);

                    $question_option = [];
                    if (sizeof($answer_order) > 0) {

                        foreach ($db_answer_order as $key => $value) {

                            if ($answer_order[$key] != $value) {
                                $question_option[] = $value;
                            }
                        }
                    }
                }


                $evaluation_status = ["assessment_status" =>
                    $this->question_evaluation($assessment_type, $assessment, $request)];

                $question_option = $this->question_option($assessment);

                $evaluation_status['correctanswer'] = "Correct answer is - " . implode(',', $question_option);

                return $this->apiResposeHandler->returnResponse(
              
                    $this->apiResposeHandler->responseStatus['success'],                 

                    $evaluation_status
                );
            }
        }

        if (($assessment_type == "mcq" || $assessment_type == "draganddrop") && !$teacher_attempt && $max_available_point == 0) {


            if ($request->get('challange')) {

                $from_challange = true;

            } else {


                $evaluation_status = ["assessment_status" =>
                    $this->question_evaluation($assessment_type, $assessment, $request)];

                $question_option = $this->question_option($assessment);

                $evaluation_status['correctanswer'] = "Correct answer is - " . implode(',', $question_option);

                return $this->apiResposeHandler->returnResponse(
                    $this->apiResposeHandler->responseStatus['success'],                   
                    $evaluation_status               
                );
            }
        }


        /* calculate point based on assessment type*/

        /* if teacher does not attempt then we will calculate point here */

        if (!$teacher_attempt) {

            $request->request->add(['point' => 0]);
        }

        /* mcq question */

        if ($assessment_type == "draganddrop" || $assessment_type == "mcq") {

            /* all assessment options */
            $question_option = [];
            foreach ($assessment->assessment_option as $item) {

                if ($item->is_correct == 1) {
                    $question_option[$item->id] = $item->is_correct;
                }

            }


            $assessment_status = 'fail';

            if ($request->get('assesment_option_id')) {

                $student_answer = $request->get('assesment_option_id');

                /* Student Must select maximum correct answer
                   Not more than that
                    possible scenerio --- 2 corect answer i have selected random 3 or i have selected every option then it will prevent student attempt with penalty
                */

                if (($assessment_type != "free_text" || $assessment_type != "short_free_text") && !$teacher_attempt && count($student_answer) > count($question_option)) {  

                    $prevent_false_attempt_with_penalty = count($question_option);

                    /* fail attempt with penalty */

                    $max_available_point = (float)$max_available_point - (2);

                    if ($max_available_point < 0) {

                        $max_available_point = 0;
                    }

                } else {

                    if ($assessment_type == "draganddrop") {

                        $answer_order = $request->get('dragdrop_answer_order');

                        $answer_order = (array)json_decode($answer_order);

                        $db_answer_order = json_decode($assessment->dragdrop_answer_order);

                        $question_option = [];
                        foreach ($db_answer_order as $key => $value) {

                            if ($answer_order[$key] != $value) {
                                $question_option[] = $value;
                            }
                        }

                    } else {

                        foreach ($student_answer as $key => $optionId) {

                            /* calculate point based on attempt */

                            unset($question_option[$optionId]);
                        }
                    }

                    /* all option answered correctly by student */
                    if (count($question_option) == 0) {

                        $request->request->add(['point' => $max_available_point]);
                        $assessment_status = 'passed';
                    }

                    /* failed to answer correctly */
                    if (count($question_option) > 0) {

                        /* here hard coded penalty 2 we will take it from the class where this assessment belongs
                        we will also take attempt from it and put it in assessment
                        */

                        $max_available_point = (float)$max_available_point - (2);

                        if ($max_available_point < 0) {

                            $max_available_point = 0;

                        }
                    }
                }
            }

            if ($request->get('can_skip')) {

                $assessment_status = 'pending';

                $max_available_point = (float)$max_available_point - (2);

                if ($no_of_attempts >= $aseesment_attempt) {

                    $max_available_point = 0;

                    $assessment_status = 'fail';
                }
            }
        }

        /* free text question */
        if (($assessment_type == "free_text" || $assessment_type == "short_free_text")) {

            if (!$teacher_attempt) {
                $assessment_status = 'pending';
            }
            /* otherwise teacher will provide assessmant sattus
               by evaluating free_text.
            */
        }


        if ($from_challange) {

            $max_available_point = 0;
            $assessment_status = 'fail';
        }

        $request->request->add(['max_available_point' => $max_available_point]);

        $arrFieldsToSave[] = 'max_available_point';

        $request->request->add(['assessment_status' => $assessment_status]);

        $request->request->add(['time_taken' => $request->get('answer_time')]);

        $arrFieldsToSave[] = 'assessment_status';
        $arrFieldsToSave[] = 'time_taken';

        /*setting optional from field if request has value of that kind */

        $optional_field = ['time_taken', 'approval_status', 'is_answered'];

        foreach ($optional_field as $value) {

            if ($request->get($value)) {

                $arrFieldsToSave[] = $value;
            }
        }

        if ($request->get('teacher_attempt_evaluate')) {


            $evaluation_status = ["assessment_status" =>
                $request->get('assessment_status')];

            return $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['success'],

                $evaluation_status           
            );
        }

        $modelData = $this->apiCommonProcessHandler->saveModelDataByModel($request, 'assesment_result', AssessmentResult::class, $arrFieldsToSave);

        if ($modelData['status'] == $this->apiResposeHandler->responseStatus['success']) {
            /**
             * earned badge
             */
            if ($modelData['content']['assessment_status'] == PASSED) {

                $returned_correct_answer = $this->getCorrectAnswerBadge($class_id, $student_id);
                if (isset($returned_correct_answer)) {
                    $earned_batch_array[] = $returned_correct_answer;
                }

                /**
                 * get badge when answered within 20 seconds
                 */
                if ($request->has('answer_time') && $request->answer_time <= 20) {
                    $returned_sledge_badge = $this->achievedBadged('Sledge', $class_id, $student_id);
                    if (isset($returned_sledge_badge)) {
                        $earned_batch_array[] = $returned_sledge_badge;
                    }
                }
            }

            /**
             * end earned badge
             */

            $teacher_comment = "";

            if ($request->get('teacher_comment')) {

                $teacher_comment = $request->get('teacher_comment');
            }


            if ($request->get('answer_text')) {

                $this->addAssessmentOptionResultByAnswertext($modelData['content']['id'], $request->get('answer_text'), $no_of_attempts, $teacher_comment);
            }


            //Add AssessmentResult relation
            if ($request->get('assesment_option_id')) {
                // $this->clearAssessmentOptionResultById('assesment_result_id', $modelData['content']['id']);

                foreach ($request->get('assesment_option_id') as $optionId) {
                    //if(array_key_exists($key, )
                    $this->addAssessmentOptionResult($modelData['content']['id'],
                        $optionId, $no_of_attempts);
                }
            }

            if ($request->has('teacher_review')) {
                //Send users
                $modelData['content']['options'] = AssessmentResult::find($modelData['content']['id'])->assessment_answer_result;
                //$modelData['content']['options'] = AssessmentResult::where('id', $modelData['content']['id'])->first();
                $modelData['content']['assessment'] = Assessment::find($request->assessment_id);

                $modelData['content']['answer_text'] = AssessmentResultAnswerPivot::where(
                    'assesment_result_id', $modelData['content']['id'])->where('answer_text', '<>', '')->first();
            } else {
                $modelData['content']['options'] = AssessmentResult::find($modelData['content']['id'])->assessment_answer_result;

                $modelData['content']['answer_text'] = AssessmentResultAnswerPivot::where(
                    'assesment_result_id', $modelData['content']['id'])->where('answer_text', '<>', '')->get();
            }

        }


        /**
         * topic and section completed badges
         */

        $return_section_topic_bade = $this->getTopicSectionCompletedBadge($class_id, $student_id);
        if (sizeof($return_section_topic_bade) > 0) {
            foreach ($return_section_topic_bade as $key => $value) {
                if ($value != null) {
                    $earned_batch_array[] = $value;
                }
            }
        }

        /**
         * ranks first in leaderboard badge
         */
        if (isset($modelData['status']) && $modelData['status'] == $this->apiResposeHandler->responseStatus['success']) {
            if (isset($modelData['content']['assessment_status']) && $modelData['content']['assessment_status'] == PASSED) {
                $return_leaderboard_badge = $this->getLeaderboardBadge($class_id, $student_id);
                if (isset($return_leaderboard_badge)) {
                    $earned_batch_array[] = $return_leaderboard_badge;
                }
            }
        }

        if ($prevent_false_attempt_with_penalty) {

            /* choose more option to match multiple corrected answer */
            return $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['error'],
                $this->apiResposeHandler->makeMessage('Please select at most ' . $prevent_false_attempt_with_penalty)
            );

        }

        $modelData['content']['badge'] = $earned_batch_array;
        return $modelData;
    }

    /**
     * @param $class_id
     * @param $student_id
     * @return mixed
     * get leader board top ranked badge
     */
    public function getLeaderboardBadge($class_id, $student_id)
    {
        $objStudent = SampleApi::post('leaderboard', ['class_id' => $class_id, 'parent_id' => null, 'orderBy' => 'DESC', 'limit' => 1]);
        if ($objStudent->status == API_RESPONSE_SUCCESS) {
            $topStudent = $objStudent->content;
            foreach ($topStudent as $std) {
                if ($std->student_id == $student_id) {
                    return $this->achievedBadged('Mountain', $class_id, $student_id);
                }
            }
        }
    }

    /**
     * @param $class_id
     * @param $student_id
     * @return array
     * get topic and section completed badge
     */
    public function getTopicSectionCompletedBadge($class_id, $student_id)
    {
        $badges_data = [];
        $student_assigned_classes = ClassStudentsPivot::join('profiles', 'class_student_pivot.student_id', '=', 'profiles.id')
            ->where('class_student_pivot.student_id', $student_id)
            ->where('class_student_pivot.class_id', $class_id)// new add
            ->orderBy('created_at', 'ASC')
            ->get();

        if (sizeof($student_assigned_classes) > 0) {
            foreach ($student_assigned_classes as $class) {

                $completedSection = 0;
                $completedTopic = 0;

                $sections = Section::where('class_id', $class->class_id)
                    ->where('parent_id', '=', NULL)
                    ->orderBy('created_at', 'ASC')
                    ->get();

                if (sizeof($sections) > 0) {
                    foreach ($sections as $section) {

                        $total_content_this_section = 0;
                        $total_answerd_content_this_section = 0;

                        $topics = Section::where('parent_id', '=', $section->id)
                            ->orderBy('created_at', 'ASC')
                            ->get();

                        if (sizeof($topics) > 0) {

                            $topicCount = 0;
                            foreach ($topics as $topic) {

                                /**
                                 * chatty badge for 10 discussion threads
                                 */
                                $discussionThreads = DB::table('discussions')
                                    ->join('assessments', 'discussions.assessment_id', '=', 'assessments.id')
                                    ->join('sections', 'assessments.section_id', '=', 'sections.id')
                                    ->where('assessments.section_id', $topic->id)
                                    ->where('discussions.discussion_user_id', $student_id)
                                    ->count();

                                if ($discussionThreads == 10) {
                                    $badges_data[] = $this->achievedBadged('Noisy Pitta', $class_id, $student_id);
                                }

                                $total_content_this_topic = TopicContent::where('topic_id', $topic->id)->count();
                                $answerd_assessment_this_topic = DB::table('assesment_result')
                                    ->join('assessments', 'assesment_result.assessment_id', '=', 'assessments.id')
                                    ->join('sections', 'assessments.section_id', '=', 'sections.id')
                                    ->where('assessments.section_id', $topic->id)
                                    ->where('assesment_result.student_id', $student_id);
                                // ->count();

                                $instruction_view_this_topic = DB::table('instructions_view')
                                    ->join('instructions', 'instructions_view.instruction_id', '=', 'instructions.id')
                                    ->join('sections', 'instructions.section_id', '=', 'sections.id')
                                    ->where('instructions.section_id', $topic->id)
                                    ->where('instructions_view.student_id', $student_id)
                                    ->count();

                                $count_total_answerd_this_topic = $instruction_view_this_topic + $answerd_assessment_this_topic->count();
                                /**
                                 * get streak badge
                                 */
                                $free_text_type_id = AssessmentType::where('name', 'free_text')->first()->id;
                                $consecuitive_answerd_assessment_this_class = DB::table('assesment_result')
                                    ->join('assessments', 'assesment_result.assessment_id', '=', 'assessments.id')
                                    ->join('sections', 'assessments.section_id', '=', 'sections.id')
                                    ->where('sections.class_id', $class_id)
                                    ->where('assesment_result.student_id', $student_id)
                                    ->where('assessments.assessment_type_id', '!=', $free_text_type_id);

                                if ($consecuitive_answerd_assessment_this_class->count() >= 3) {

                                    $assessment_status = [];
                                    foreach ($consecuitive_answerd_assessment_this_class->get(['assesment_result.assessment_status']) as $key => $ass_sts) {

                                        $assessment_status[] = $ass_sts->assessment_status;
                                    }

                                    if (count($assessment_status) > 0) {
                                        if ($consecuitive_answerd_assessment_this_class->count() == 3 || $consecuitive_answerd_assessment_this_class->count() == 5 || $consecuitive_answerd_assessment_this_class->count() == 10 || $consecuitive_answerd_assessment_this_class->count() == 20) {
                                            if (!in_array(PENDING, $assessment_status) && !in_array(FAIL, $assessment_status)) {

                                                $badges_data[] = $this->achievedStreakBadged(StreakBadge[$consecuitive_answerd_assessment_this_class->count()], $class_id, $student_id);
                                            }
                                        }
                                    }
                                }

                                if ($total_content_this_topic > 0 && $total_content_this_topic == $count_total_answerd_this_topic) {

                                    $completedTopic++;
                                    $topicCount++;
                                }

                                /**
                                 * get topic completed badge
                                 */
                                if ($completedTopic == 1 || $completedTopic == 2 || $completedTopic == 3 || $completedTopic == 5 || $completedTopic == 10 || $completedTopic == 20) {

                                    $badges_data[] = $this->achievedBadged(TopicCompleteBadge[$completedTopic], $class_id, $student_id);
                                }

                                /**
                                 * get section completed badge total_completed_content_this_section
                                 */
                                if (sizeof($topics) > 0 && sizeof($topics) == $topicCount) {

                                    $completedSection++;
                                    if ($completedSection <= 12) {
                                        $badges_data[] = $this->achievedBadged(SectionCompleteBadge[$completedSection], $class_id, $student_id);

                                    }
                                }

                            }
                        }
                    }
                }
            }
        }

        return $badges_data;
    }

    /**
     * @param $class_id
     * @param $student_id
     * @return mixed
     * get correct answer badge
     */
    public function getCorrectAnswerBadge($class_id, $student_id)
    {
        $countPassedResult = DB::table('assesment_result')
            ->join('assessments', 'assesment_result.assessment_id', '=', 'assessments.id')
            ->join('sections', 'assessments.section_id', '=', 'sections.id')
            ->where('sections.class_id', $class_id)
            ->where('assesment_result.assessment_status', PASSED)
            ->where('assesment_result.student_id', $student_id)
            ->count();

        if ($countPassedResult == 1 || $countPassedResult == 2 || $countPassedResult == 3 || $countPassedResult == 5 || $countPassedResult == 10 || $countPassedResult == 20 || $countPassedResult == 50) {

            return $this->achievedBadged(CorrectAnswerBadge[$countPassedResult], $class_id, $student_id);
        }
    }

    /**
     * @param $assessment
     * @return array
     * get question option
     */
    public function question_option($assessment)
    {

        $question_option = [];
        foreach ($assessment->assessment_option as $item) {

            if ($item->is_correct == 1) {
                $question_option[$item->id] = $item->title;
            }
        }

        return $question_option;
    }

    /**
     * @param $assessment_type
     * @param $assessment
     * @param $request
     * @return string
     * question evaluation part
     */
    public function question_evaluation($assessment_type, $assessment, $request)
    {
        $assessment_status = 'fail';

        if ($assessment_type == "draganddrop" || $assessment_type == "mcq") {

            /* all assessment options */
            $question_option = [];
            foreach ($assessment->assessment_option as $item) {

                if ($item->is_correct == 1) {
                    $question_option[$item->id] = $item->is_correct;
                }
            }

            if ($request->get('assesment_option_id')) {

                $student_answer = $request->get('assesment_option_id');

                if (count($student_answer) > count($question_option)) {

                    return $assessment_status;

                } else {

                    if ($assessment_type == "draganddrop") {

                        $answer_order = $request->get('dragdrop_answer_order');

                        $answer_order = (array)json_decode($answer_order);

                        $db_answer_order = json_decode($assessment->dragdrop_answer_order);

                        $question_option = [];
                        foreach ($db_answer_order as $key => $value) {

                            if ($answer_order[$key] != $value) {
                                $question_option[] = $value;
                            }
                        }
                    }

                    foreach ($student_answer as $key => $optionId) {

                        /* calculate point based on attempt */

                        unset($question_option[$optionId]);
                    }

                    /* all option answered correctly by student */
                    if (count($question_option) == 0) {


                        $assessment_status = 'passed';
                    }
                }
            }
        }
        return $assessment_status;

    }

    /**
     * @param Request $request
     * @return \App\Repository\Api\jSon|Array
     * get free text answer point
     */
    public function answerTextPoint(Request $request)
    {
        if (!$request->get('class_id') || !$request->get('user_id')) {
            return $this->apiResposeHandler->returnResponse(

                $this->apiResposeHandler->responseStatus['error'],
                $this->apiResposeHandler->makeMessage('Please provide User Id and Class Id')
            );
        }       

        return $this->saveAssessmentResultData($request, true);
    }


    /**
     * Delete AssessmentResult data
     *
     * @param Request $request
     *
     * @return Array
     */
    public function deleteAssessmentResult(Request $request)
    {
        return $this->apiCommonProcessHandler->deleteModelDataById($request, AssessmentResult::class);
    }

    /**
     * Get AssessmentOption data
     *
     * @param Request $request
     *
     * @return Array
     */
    public function assessmentOptionList(Request $request)
    {

        if ($request->get('assessment_id')) {

            $request_array = $request->all();
            $request_array['where'] = ['assessment_id' => $request->get('assessment_id')];
            $request->replace($request_array);

        } else {
            return $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['error'],
                'Assessment Id is required for Assessment Option List'
            );
        }

        $modelData = $this->apiCommonProcessHandler->getModelListByModel(AssessmentOption::class, $request);

        return $modelData;
    }

    /**
     * Save AssessmentOption data
     *
     * @param Request $request
     *
     * @return Array
     */
    public function saveAssessmentOptionData(Request $request)
    {
        $arrFieldsToSave = ['title', 'assessment_id', 'is_correct', 'img_answer'];
        $modelData = $this->apiCommonProcessHandler->saveModelDataByModel($request, 'assessments_options', AssessmentOption::class, $arrFieldsToSave);

        return $modelData;
    }

    /**
     * Delete AssessmentOption data
     *
     * @param Request $request
     *
     * @return Array
     */
    public function deleteAssessmentOption(Request $request)
    {
        return $this->apiCommonProcessHandler->deleteModelDataById($request, AssessmentOption::class);
    }

    /**
     * @param Request $request
     * @return \App\Repository\Api\Array|\App\Repository\Api\jSon
     * save instruction view data
     */
    public function saveInstructionViewData(Request $request)
    {
        $arrFieldsToSave = ['instruction_id', 'student_id', 'point'];

        if ($request->get('user_id') && $request->get('instruction_id')) {
            $request_array = $request->all();
            $request_array['student_id'] = $request->get('user_id');
            $request->replace($request_array);


            $instruction = Instruction::find($request->get('instruction_id'));
            if ($instruction) {
                $request_array = $request->all();
                $request_array['point'] = $instruction->point;
                $request->replace($request_array);

                InstructionStudentPivot::where(['instruction_id' => $request->get('instruction_id'), 'student_id' => $request->get('user_id')])->delete();
            } else {
                return $this->apiResposeHandler->returnResponse(
                    $this->apiResposeHandler->responseStatus['error'],
                    'Invalid instruction id.'
                );
            }
        } else {
            return $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['BadRequest'],
                'Please provide instruction id and user id.'
            );
        }

        $modelData = $this->apiCommonProcessHandler->saveModelDataByModel($request, 'instructions_view', InstructionStudentPivot::class, $arrFieldsToSave, false, true);

        $earned_batch_array = [];
        $class = DB::table('topic_content')
            ->join('sections', 'topic_content.topic_id', '=', 'sections.id')
            ->where('topic_content.content_mapping_id', $request->get('instruction_id'))
            ->first(['sections.class_id']);

        if (isset($class->class_id)) {

            $return_section_topic_bade = $this->getTopicSectionCompletedBadge($class->class_id, $request->get('user_id'));
            if (sizeof($return_section_topic_bade) > 0) {
                foreach ($return_section_topic_bade as $key => $value) {
                    if ($value != null) {
                        $earned_batch_array[] = $value;
                    }
                }
            }
        }

        $modelData['content']->badge = $earned_batch_array;
        return $modelData;
    }


    /**
     * Get Single Question By Id
     * Current User Id
     * @param Request $request
     *
     * @return Array
     */
    public function getSingleQuestion(Request $request)
    {

        if ($request->get('question_id')) {

            $question_id = $request->get('question_id');

        } else {
            return $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['error'],
                'Assessment Id is required for Assessment Option List'
            );
        }

        try {
            $model = new Assessment();

            $sortOrder = 'title';
            $sortDirection = 'ASC';

            if ($request->get('sortOrder')) {
                $sortOrder = $request->get('sortOrder');
            }

            if ($request->get('sortDirection')) {
                $sortDirection = $request->get('sortDirection');
            }

            $modelData = Assessment::with('assessment_option', 'assessment_type')->find($question_id);

        } catch (\Exception $ex) {
            //otherwise return error
            return $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['error'],
                $ex->getMessage()
            );
        }
        if ($modelData) {
            //Return faculty object
            $modelData = $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['success'],
                $modelData
            );

            return $modelData;
        } else {
            //otherwise return error
            return $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['error'],
                $this->apiResposeHandler->errorListMessage()
            );
        }
    }

    /**
     * get revision
     *
     * @param Request $request
     *
     * @return Array
     */
    public function revisionSaveQuestion(Request $request)
    {
        /* mandatory assessment_id from api input */
        if (!$request->get('assessment_id')) {
            return $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['error'],
                $this->apiResposeHandler->makeMessage('Assessment Id Required.')
            );
        }

        /* mandatory fields and will be added gradually */
        $arrFieldsToSave = ['student_id', 'assessment_id', 'point'];

        $assessment_id = $request->get('assessment_id');
        $assessment = Assessment::with('assessment_option', 'assessment_type')->find($assessment_id);

        $aseesment_attempt = $assessment->no_of_attempts;
        $assessment_type = $assessment->assessment_type->name;

        $teacher_attempt = false;

        if (($assessment_type == "free_text" || $assessment_type == "short_free_text") && !$teacher_attempt && !$request->get('answer_text')) {

            return $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['error'],
                $this->apiResposeHandler->makeMessage('Must provide Answer Text For Free text Question')
            );
        }

        if ($assessment_type == "draganddrop"
            && !$teacher_attempt && !$request->get('dragdrop_answer_order')
        ) {

            return $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['error'],
                $this->apiResposeHandler->makeMessage('Must provide Drag And Drop Sequence')
            );
        }

        //draganddrop
        /* can skip will allow this attempt but a penalty will be made */
        if (($assessment_type == "mcq" || $assessment_type == "draganddrop") && !$teacher_attempt

            && !$request->get('can_skip')
            && !$request->get('assesment_option_id')
        ) {

            return $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['error'],
                $this->apiResposeHandler->makeMessage('Must provide Atleast One Option For Multiple Choice Question')
            );
        }

        /*
            if any student choose all answer or select options more than correct answer
        */
        $prevent_false_attempt_with_penalty = false;


        /* attempt calcualtion */

        $no_of_attempts = 1;

        $max_available_point = $assessment->point;       

        /* mcq question */

        if ($assessment_type == "draganddrop" || $assessment_type == "mcq") {

            /* all assessment options */
            $question_option = [];
            foreach ($assessment->assessment_option as $item) {

                if ($item->is_correct == 1) {
                    $question_option[$item->id] = $item->is_correct;
                }
            }

            $assessment_status = 'fail';

            if ($request->get('assesment_option_id')) {

                $student_answer = $request->get('assesment_option_id');

                /* Student Must select maximum correct answer
                   Not more than that
                    possible scenerio --- 2 corect answer i have selected random 3 or i have selected every option then it will prevent student attempt with penalty
                */

                if (($assessment_type != "free_text" || $assessment_type != "short_free_text") && !$teacher_attempt && count($student_answer) > count($question_option)) { 

                    $prevent_false_attempt_with_penalty = count($question_option);
                    /* fail attempt with penalty */

                    $max_available_point = (float)$max_available_point - (2);

                    if ($max_available_point < 0) {

                        $max_available_point = 0;
                    }

                } else {

                    if ($assessment_type == "draganddrop") {

                        $answer_order = $request->get('dragdrop_answer_order');

                        $answer_order = (array)json_decode($answer_order);

                        $db_answer_order = json_decode($assessment->dragdrop_answer_order);

                        $question_option = [];
                        foreach ($db_answer_order as $key => $value) {

                            if ($answer_order[$key] != $value) {
                                $question_option[] = $value;
                            }
                        }

                    } else {

                        foreach ($student_answer as $key => $optionId) {

                            /* calculate point based on attempt */

                            unset($question_option[$optionId]);
                        }
                    }

                    /* all option answered correctly by student */
                    if (count($question_option) == 0) {

                        $request->request->add(['point' => $max_available_point]);

                        $assessment_status = 'passed';
                    }

                    /* failed to answer correctly */
                    if (count($question_option) > 0) {

                        /* here hard coded penalty 2 we will take it from the class where this assessment belongs
                        we will also take attempt from it and put it in assessment
                        */

                        $max_available_point = (float)$max_available_point - (2);

                        if ($max_available_point < 0) {

                            $max_available_point = 0;

                        }
                    }
                }
            }

        }

        /* free text question */
        if (($assessment_type == "free_text" || $assessment_type == "short_free_text")) {

            if (!$teacher_attempt) {
                $assessment_status = 'pending';
            }

            /* otherwise teacher will provide assessmant sattus
               by evaluating free_text.
            */

        }

        if ($assessment_status == 'fail') {


            $section_id = $request->get('section_id') ? $request->get('section_id') : $request->get('class_id');

            $whereQ = [
                'section_id' => $section_id,
                'user_id' => $request->get('student_id'),
            ];

            $revision = Revision::where($whereQ)->first();         


            if (!$revision) {

                $whereQ['wrong_answers'] = json_encode([$request->get('assessment_id')]);

                Revision::create($whereQ);

            } else {

                $wrongA = json_decode($revision->wrong_answers);

                $aid = $request->get('assessment_id');

                if (!Arrays::contains($wrongA, $aid)) {

                    $wrongA[] = $aid;
                }

                $revision->wrong_answers = json_encode($wrongA);
                $revision->save();
            }

        }

        $data = array(

            'student_id' => $request->get('student_id'),
            'assessment_id' => $request->get('assessment_id'),
            'point' => $max_available_point,

            'assessment_status' => $assessment_status,

            'approval_status' => 1,

            'is_answered' => 1,
        );

        return $this->apiResposeHandler->returnResponse(
            $this->apiResposeHandler->responseStatus['success'],
            $data
        );       

    }

    /**
     * @param Request $request
     * @return mixed|\App\Repository\Api\jSon
     * get rtevision list
     */
    public function revisionList(Request $request)
    {
        $section_id = "";

        $class_id = "";

        /* section id for revising inside topic

            class id for revising inside class Tab
        */

        if (!$request->get('section_id') && !$request->get('class_id'))
            return $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['error'],
                'Either Section Id or class id required for Assessment List'
            );

        if ($request->get('section_id')) {

            $section_id = $request->get('section_id');
        }

        if ($request->get('class_id')) {

            $class_id = $request->get('class_id');
        }

        try {
            $model = new Assessment();

            /*send first 5 comment that has no child*/
            $skip = 0;
            $take = 1;

            if ($request->get('from')) {
                $skip = $request->get('from');
            }

            if ($request->get('to')) {
                $take = $request->get('to');
            }

            //$skip = $skip -1;
            if ($skip < 0) $skip = 0;

            $wrongA = $request->get('wrong_answers');

            if ($request->get('class_id')) {

                if ($request->get('ques_numb')) {

                    $total_discussion = $request->get('ques_numb');
                }

                $modelData = $this->getclassQuestion($request, $total_discussion);
            }


            if ($request->get('section_id')) {

                if ($request->get('ques_numb')) {

                    $total_discussion = $request->get('ques_numb');
                }

                $modelData = $this->getQuestionData($section_id, $request, $total_discussion, $skip, $wrongA, true);

            }

        } catch (\Exception $ex) {
            //otherwise return error
            return $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['error'],
                $ex->getMessage()
            );
        }
        if ($modelData) {
            //Return faculty object
            $modelData = $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['success'],
                $modelData
            );


            if ($request->get('question_id')) {

                if ($request->get('class_id')) {

                    $data = Assessment::with('assessment_option', 'assessment_type')->find($request->get('question_id'));

                    if ($data) {

                        $data->assessment_option = $data->assessment_option;
                        $data->assessment_type = $data->assessment_type;

                        $modelData['content'][] = [$data];
                    }
                }

                if ($request->get('section_id')) {

                    $data = $this->getQuestionData($request->get('section_id'), $request);

                    $modelData['content'][] = $data;
                }
            }

            $modelData['total_question'] = $total_discussion;
            $modelData['from'] = (int)$request->get('from');
            $modelData['to'] = (int)$take;
            return $modelData;
        } else {
            //otherwise return error
            return $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['error'],
                $this->apiResposeHandler->errorListMessage()
            );
        }
    }

    /**
     * @param $request
     * @param $total_discussion
     * @return mixed
     * get question list of class
     */
    public function getclassQuestion($request, $total_discussion)
    {
        $sortOrder = 'title';
        $sortDirection = 'ASC';
        $class_id = $request->get('class_id');
        $id_list = $request->get('wrong_answers') ? json_decode($request->get('wrong_answers')) : [];

        $modelData1 = [];

        if (count($id_list) > 0) {

            $modelData1 = DB::table('sections')
                ->join('assessments', 'sections.id', '=',
                    'assessments.section_id')
                ->select('assessments.*')
                ->where('sections.class_id', '=', $class_id)
                ->whereIn('assessments.id', $id_list)
                ->orderBy('assessments.' . $sortOrder, $sortDirection)
                ->get();
        }

        $take = $total_discussion;
        $take = $take - count($modelData1);

        $modelData2 = DB::table('sections')
            ->join('assessments', 'sections.id', '=',
                'assessments.section_id')
            ->select('assessments.*')
            ->where('sections.class_id', '=', $class_id)
            ->whereNotIn('assessments.id', $id_list)
            ->orderBy('assessments.' . $sortOrder, $sortDirection)
            ->take($take)
            ->get();       

        if (count($id_list) > 0) {

            $modelData = $modelData1->merge($modelData2);
        } else {

            $modelData = $modelData2;
        }

        foreach ($modelData as $ind => $item) {

            $data = Assessment::with('assessment_option', 'assessment_type')->find($item->id);

            if ($data) {

                $modelData[$ind]->assessment_option = $data->assessment_option;
                $modelData[$ind]->assessment_type = $data->assessment_type;
            }
        }
        return $modelData;
    }

    /**
     * @param Request $request
     * @return \App\Repository\Api\jSon
     * revision reset
     */
    public function revisionReset(Request $request)
    {
        $whereQ = [
            'section_id' => $request->get('section_id'),
            'user_id' => $request->get('user_id'),
        ];

        $revision = Revision::where($whereQ)->first();     

        $wrongA = [];

        if (!$revision) {

            $whereQ['wrong_answers'] = json_encode([]);
            Revision::create($whereQ);

        } else {

            $wrongA = json_decode($revision->wrong_answers);

            $revision->wrong_answers = json_encode([]);
            $revision->save();
        }

        return $this->apiResposeHandler->returnResponse(
            $this->apiResposeHandler->responseStatus['success'],
            $wrongA

        );

    }

    /**
     * Difficult Question
     * class_id based on Atempt
     * @param  Request $request
     *
     * @return Array ApiInstructionGraphProvider
     */

    public function difficultQuestion(Request $request)
    {

        if (!$request->get('class_id'))
            return $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['error'],
                'class id required for Assessment List'
            );


        $modelData = DB::table('sections')
            ->join('assessments', 'sections.id', '=',
                'assessments.section_id')
            //->select('assessments.id')
            ->where('sections.class_id', '=', $request->get('class_id'))
            ->pluck('assessments.id')->toArray();


        $modelData = DB::table('assessments')
            ->join('assesment_result', 'assessments.id', '=',
                'assesment_result.assessment_id')
            ->select('assessments.*')
            ->where('assesment_result.no_of_attempts', '>', 1)
            ->whereIn('assessments.id', $modelData)
            ->orderBy('assesment_result.no_of_attempts', 'DESC')
            ->get();       

        return $this->apiResposeHandler->returnResponse(
            $this->apiResposeHandler->responseStatus['success'],
            $modelData

        );

    }

    /**
     * @param Request $request
     * @return \App\Repository\Api\jSon
     * get question list of topic
     */
    public function topicQuestionList(Request $request)
    {

        if (!$request->get('section_id')) {

            return $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['error'],
                'Section id required for Assessment List'
            );
        }

        if ($request->get('user_role') == 'teacher') {

            $topic_data = TopicContentCustom::where('parent_id', '=', NULL)
                ->where('topic_id', $request->section_id)             
                ->orderBy('order_dnd', 'ASC')
                ->orderBy(DB::raw("CASE WHEN topic_content.schedule_date IS NULL THEN CAST(topic_content.send_date AS UNSIGNED) ELSE CAST(topic_content.schedule_date AS DATE) END "), 'ASC')
                ->orderBy(DB::raw("CASE WHEN topic_content.schedule_time  IS NULL THEN CAST(topic_content.send_time AS UNSIGNED) ELSE CAST(topic_content.schedule_time AS UNSIGNED) END "), 'ASC')
                ->get();

            foreach ($topic_data as $key => $value) {

                $topic_data[$key]['child'] = TopicContentCustom::with('question', 'instruction')
                    ->where(
                        'parent_id',
                        '=', $value->id
                    )                   
                    ->orderBy('order_dnd', 'ASC')
                    ->get();
            }

            $dataNew = [];
            foreach ($topic_data as $key => $value) {
                if ($value->content_type == 'question') {
                    $dataNew[] = $value->content_mapping_id;
                }

                if ($value->child) {
                    foreach ($value->child as $key => $child_value) {
                        if ($child_value->content_type == 'question') {
                            $dataNew[] = $child_value->content_mapping_id;
                        }
                    }
                }
            }

            $modelDataResult = DB::table('topic_content')
                ->join('assessments',
                    'topic_content.content_mapping_id',
                    '=', 'assessments.id')
                ->join('assessments_types', 'assessments_types.id', '=', 'assessments.assessment_type_id')
                ->leftJoin('assesment_result', function ($join) use ($request) {

                    $join->on('topic_content.content_mapping_id', '=', 'assesment_result.assessment_id')
                        ->where('assesment_result.student_id', $request->get('user_id'));
                })
                ->where('topic_content.content_type', 'question')                
                ->whereIn('topic_content.content_mapping_id', $dataNew)
                ->where('topic_content.topic_id', $request->section_id)
                ->select('assessments.*', 'topic_content.content_mapping_id', 'topic_content.schedule_date', 'topic_content.order_dnd as orderdnd', 'assessments_types.name as questiontype', 'assesment_result.point as scorepoint',
                    'assesment_result.no_of_attempts as attemptTried',
                    'assesment_result.assessment_status as assessmentstatus', 'topic_content.send_status')               
                ->get();

            $modelData = [];
            foreach ($dataNew as $key => $data_value) {
                $modelData[] = $modelDataResult->where('content_mapping_id', $data_value)->first();
            }

        } else {

            $topic_data = TopicContentCustom::where('parent_id', '=', NULL)
                ->where('topic_id', $request->section_id)
                ->where('send_status', 1)
                ->orderBy('order_dnd', 'ASC')
                ->orderBy(DB::raw("CASE WHEN topic_content.schedule_date IS NULL THEN CAST(topic_content.send_date AS UNSIGNED) ELSE CAST(topic_content.schedule_date AS DATE) END "), 'ASC')
                ->orderBy(DB::raw("CASE WHEN topic_content.schedule_time  IS NULL THEN CAST(topic_content.send_time AS UNSIGNED) ELSE CAST(topic_content.schedule_time AS UNSIGNED) END "), 'ASC')
                ->get();

            foreach ($topic_data as $key => $value) {

                $topic_data[$key]['child'] = TopicContentCustom::with('question', 'instruction')
                    ->where(
                        'parent_id',
                        '=', $value->id
                    )
                    ->where('send_status', 1)
                    ->orderBy('order_dnd', 'ASC')
                    ->get();
            }

            $dataNew = [];
            foreach ($topic_data as $key => $value) {
                if ($value->content_type == 'question') {
                    $dataNew[] = $value->content_mapping_id;
                }

                if ($value->child) {
                    foreach ($value->child as $key => $child_value) {
                        if ($child_value->content_type == 'question') {
                            $dataNew[] = $child_value->content_mapping_id;
                        }
                    }
                }
            }

            $modelDataResult = DB::table('topic_content')
                ->join('assessments',
                    'topic_content.content_mapping_id',
                    '=', 'assessments.id')
                ->join('assessments_types', 'assessments_types.id', '=', 'assessments.assessment_type_id')
                ->leftJoin('assesment_result', function ($join) use ($request) {

                    $join->on('topic_content.content_mapping_id', '=', 'assesment_result.assessment_id')
                        ->where('assesment_result.student_id', $request->get('user_id'));
                })
                ->where('topic_content.content_type', 'question')
                ->where('topic_content.send_status', 1)
                ->whereIn('topic_content.content_mapping_id', $dataNew)
                ->where('topic_content.topic_id', $request->section_id)
                ->select('assessments.*', 'topic_content.content_mapping_id', 'topic_content.schedule_date', 'topic_content.order_dnd as orderdnd', 'assessments_types.name as questiontype', 'assesment_result.point as scorepoint',
                    'assesment_result.no_of_attempts as attemptTried',
                    'assesment_result.assessment_status as assessmentstatus', 'topic_content.send_status')
                //->orderBy('orderdnd','ASC')          
                ->get();

            $modelData = [];
            foreach ($dataNew as $key => $data_value) {
                $modelData[] = $modelDataResult->where('content_mapping_id', $data_value)->first();
            }
        }

        if ($request->get('question_id')) {

            $question_id = $request->get('question_id');


            $data = AssessmnetCustom::with('assessment_option', 'assessment_type')->find($question_id);


            $myattempt = AssessmentResult::where('assessment_id', $question_id)->where('student_id', $request->get('user_id'))->first();

            $attemptTried = $myattempt ? $myattempt->no_of_attempts : 0;
            $assessmentstatus = $myattempt ? $myattempt->assessment_status : Null;
            $scorepoint = $myattempt ? $myattempt->point : 0;


            $data->attemptTried = $attemptTried;
            $data->assessmentstatus = $assessmentstatus;
            $data->scorepoint = $scorepoint;


            $modelData[] = [$data];
        }


        return $this->apiResposeHandler->returnResponse(
            $this->apiResposeHandler->responseStatus['success'],
            $modelData

        );

    }

    /**
     * @param Request $request
     * @return \App\Repository\Api\jSon
     * get all free text question list
     */
    public function getFreeTextQuestionList(Request $request)
    {
        if (!$request->get('class_id')) {
            return $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['error'],
                'Class id required'
            );
        }

        $free_text_type_id = AssessmentType::where('name', 'free_text')->first()->id;
        $modelData = DB::table('classes')
            ->join('sections', 'classes.id', '=', 'sections.class_id')
            ->join('topic_content', 'sections.id', '=', 'topic_content.topic_id')
            ->join('assessments', 'topic_content.content_mapping_id', '=', 'assessments.id')
            ->where('classes.id', $request->class_id)
            ->where('assessments.assessment_type_id', $free_text_type_id)
            ->where('sections.parent_id', '!=', NULL)
            ->where('sections.deleted_at', '=', NULL)
            ->select(['assessments.id', 'assessments.title'])
            ->get();

        if ($modelData) {
            foreach ($modelData as $ind => $item) {
                $total_student = 0;
                $not_review_student = 0;
                $review_student = 0;
                $review_pending_student = 0;

                $not_review_student = AssessmentResult::join('assesment_result_answer', 'assesment_result.id', '=', 'assesment_result_answer.assesment_result_id')
                    ->where('assesment_result.assessment_id', $item->id)
                    ->where('assesment_result.approval_status', NOT_APPROVED)                    
                    ->orderBy('assesment_result.created_at')
                    ->count();

                $review_student = AssessmentResult::join('assesment_result_answer', 'assesment_result.id', '=', 'assesment_result_answer.assesment_result_id')
                    ->where('assesment_result.assessment_id', $item->id)
                    ->where('assesment_result.approval_status', APPROVED)
                    ->orderBy('assesment_result.created_at')                  
                    ->count();

                $total_student = $not_review_student + $review_student;
                $review_pending_student = abs($total_student - $review_student);
                $modelData[$ind]->total_student = $total_student;
                $modelData[$ind]->not_review_student = $not_review_student;
                $modelData[$ind]->review_student = $review_student;
                $modelData[$ind]->review_pending_student = $review_pending_student;
            }
        }

        return $this->apiResposeHandler->returnResponse(
            $this->apiResposeHandler->responseStatus['success'],
            $modelData
        );
    }

    /**
     * @param Request $request
     * @return \App\Repository\Api\jSon
     * get unmarked students answer list
     */
    public function getAssessmentUnmarkedStudentList(Request $request)
    {
        if (!$request->assessment_id) {
            return $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['error'],
                'Assessment id required'
            );
        }

        $status = 0;
        if ($request->type == 'marked') {
            $status = APPROVED;
        }

        if (!$request->only_marked) {

            $modelData['unmarked_students'] = AssessmentResult::join('profiles', 'assesment_result.student_id', '=', 'profiles.id')
                ->where('assesment_result.assessment_id', $request->assessment_id)
                ->where('assesment_result.approval_status', $status)
                ->get(['profiles.id', 'profiles.first_name', 'profiles.last_name', 'assesment_result.id as assessment_result_id']);
        }

        $modelData['marked_students'] = $this->markedStudentList($request->assessment_id);

        return $this->apiResposeHandler->returnResponse(
            $this->apiResposeHandler->responseStatus['success'],
            $modelData
        );
    }

    /**
     * @param Request $request
     * @return \App\Repository\Api\jSon
     * get unmarked answer
     */
    public function getStudentUnmarkedAnswer(Request $request)
    {

        if (!$request->assessment_result_id) {
            return $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['error'],
                'Assessment result id required'
            );
        }

        $modelData['student_answer_one'] = AssessmentResult::join('assessments', 'assesment_result.assessment_id', '=', 'assessments.id')
            ->join('assesment_result_answer', 'assesment_result.id', '=', 'assesment_result_answer.assesment_result_id')
            ->where('assesment_result_answer.assesment_result_id', $request->assessment_result_id)
            ->first(['assessments.point', 'assessments.title', 'assesment_result_answer.answer_text', 'assesment_result.id', 'assesment_result.assessment_id', 'assesment_result.point as result_point',
                'assesment_result.approval_status']);

        $modelData['answered_student_list'] = $this->markedStudentList($modelData['student_answer_one']['assessment_id']);

        return $this->apiResposeHandler->returnResponse(
            $this->apiResposeHandler->responseStatus['success'],
            $modelData
        );
    }

    /**
     * @param $assessment_id
     * @return mixed
     * get total answered to this assessment
     */
    public function totalAnswerd($assessment_id)
    {
        return AssessmentResult::join('assessments', 'assesment_result.assessment_id', '=', 'assessments.id')
            ->join('assesment_result_answer', 'assesment_result.id', '=', 'assesment_result_answer.assesment_result_id')
            ->where('assesment_result.assessment_id', $assessment_id)
            ->count();
    }

    /**
     * Save AssessmentResult data
     *
     * @param Request $request
     *
     * @return Array
     */
    public function saveTeacherReview(Request $request)
    {
        /* mandatory assessment_id from api input */
        if (!$request->get('assessment_result_id')) {
            return $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['error'],
                $this->apiResposeHandler->makeMessage('Assessment Result Id Required.')
            );
        }

        $assessment_result = AssessmentResult::join('assessments', 'assesment_result.assessment_id', '=', 'assessments.id')
            ->where('assesment_result.id', $request->assessment_result_id)
            ->first(['assesment_result.*', 'assessments.point as assessment_point']);

        if ($assessment_result->assessment_point < $request->point) {
            return $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['error'],
                $this->apiResposeHandler->makeMessage('Sorry! Given point is greather than assessment point.')
            );
        }

        $review_info = [
            'point' => $request->point,
            'approval_status' => $request->approval_status,
            'assessment_status' => $request->assessment_status
        ];

        $modelData = null;
        $update = AssessmentResult::where('id', $request->assessment_result_id)->update($review_info);
        if ($update) {

            $modelData['marked_student_list'] = $this->markedStudentList($assessment_result->assessment_id);
            $modelData['total_answered'] = $this->totalAnswerd($assessment_result->assessment_id);

            /**
             * earned badges area
             */
            if ($request->point > 0) {

                $this->getCorrectAnswerBadge($request->class_id, $assessment_result->student_id);                
                $this->getLeaderboardBadge($request->class_id, $assessment_result->student_id);
            }


        }

        if ($modelData) {
            return $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['success'],
                $modelData
            );
        } else {
            return $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['error']
            //$this->apiResposeHandler->makeMessage('Assessment Result Id Required.')
            );
        }
    }

    /**
     * @param $assessment_id
     * @return mixed
     * get all marked student list
     */
    public function markedStudentList($assessment_id)
    {
        return AssessmentResult::join('profiles', 'assesment_result.student_id', '=', 'profiles.id')
            ->join('assesment_result_answer', 'assesment_result.id', '=', 'assesment_result_answer.assesment_result_id')
            ->where('assesment_result.assessment_id', $assessment_id)
            ->where('assesment_result.approval_status', APPROVED)
            ->orderBy('assesment_result.updated_at', 'ASC')
            ->groupBy('assesment_result.student_id')
            ->get(['assesment_result.*', 'assesment_result_answer.answer_text', 'profiles.first_name', 'profiles.last_name']);
    }

    /**
     * @param Request $request
     * @return \App\Repository\Api\jSon
     * get marked question list
     */
    public function getMarkedQuestion(Request $request)
    {
        if (!$request->get('class_id')) {
            return $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['error'],
                'Class id required'
            );
        }

        $free_text_type_id = AssessmentType::where('name', 'free_text')->first()->id;
        $modelData = DB::table('classes')
            ->join('sections', 'classes.id', '=', 'sections.class_id')
            ->join('topic_content', 'sections.id', '=', 'topic_content.topic_id')
            ->join('assessments', 'topic_content.content_mapping_id', '=', 'assessments.id')
            ->where('classes.id', $request->class_id)
            ->where('assessments.assessment_type_id', $free_text_type_id)
            ->where('sections.parent_id', '!=', NULL)
            ->where('sections.deleted_at', '=', NULL)
            ->select(['assessments.id', 'assessments.title'])
            ->get(); //dd($modelData);

        if ($modelData) {
            foreach ($modelData as $ind => $item) {

                $total_student = 0;
                $not_review_student = 0;
                $review_student = 0;
                $review_pending_student = 0;

                $not_review_student = AssessmentResult::join('assesment_result_answer', 'assesment_result.id', '=', 'assesment_result_answer.assesment_result_id')
                    ->where('assesment_result.assessment_id', $item->id)
                    ->where('assesment_result.approval_status', NOT_APPROVED)                    
                    ->orderBy('assesment_result.created_at')
                    ->count();

                $review_student = AssessmentResult::join('assesment_result_answer', 'assesment_result.id', '=', 'assesment_result_answer.assesment_result_id')
                    ->where('assesment_result.assessment_id', $item->id)
                    ->where('assesment_result.approval_status', APPROVED)
                    ->orderBy('assesment_result.created_at')                  
                    ->count();

                $total_student = $not_review_student + $review_student;
                $review_pending_student = abs($total_student - $review_student);
                $modelData[$ind]->total_student = $total_student;
                $modelData[$ind]->not_review_student = $not_review_student;
                $modelData[$ind]->review_student = $review_student;
                $modelData[$ind]->review_pending_student = $review_pending_student;
            }
        }

        return $this->apiResposeHandler->returnResponse(
            $this->apiResposeHandler->responseStatus['success'],
            $modelData
        );
    }


}