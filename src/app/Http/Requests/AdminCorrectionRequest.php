<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminCorrectionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'start_time'    => ['required', 'date_format:H:i', 'before:end_time'],
            'end_time'      => ['required', 'date_format:H:i', 'after:start_time'],

            'break1_start'  => ['nullable', 'date_format:H:i', 'before_or_equal:break1_end'],
            'break1_end'    => ['nullable', 'date_format:H:i', 'after_or_equal:break1_start'],

            'break2_start'  => ['nullable', 'date_format:H:i', 'before_or_equal:break2_end'],
            'break2_end'    => ['nullable', 'date_format:H:i', 'after_or_equal:break2_start'],

            'note'          => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $start = $this->input('start_time');
            $end = $this->input('end_time');

            foreach (['break1', 'break2'] as $break) {
                $b_start = $this->input("{$break}_start");
                $b_end   = $this->input("{$break}_end");

                if ($b_start && ($b_start < $start || $b_start > $end)) {
                    $validator->errors()->add("{$break}_start", '休憩時間が勤務時間外です。');
                }
                if ($b_end && ($b_end < $start || $b_end > $end)) {
                    $validator->errors()->add("{$break}_end", '休憩時間が勤務時間外です。');
                }
            }
        });
    }

    public function messages()
    {
        return [
            'start_time.required' => '出勤時間を入力してください。',
            'end_time.required'   => '退勤時間を入力してください。',
            'start_time.before' => '出勤時間は退勤時間より前である必要があります。',
            'end_time.after'    => '退勤時間は出勤時間より後である必要があります。',

            'break1_start.before_or_equal' => '休憩1の開始は終了より前か同じ時刻である必要があります。',
            'break1_end.after_or_equal'    => '休憩1の終了は開始より後か同じ時刻である必要があります。',
            'break2_start.before_or_equal' => '休憩2の開始は終了より前か同じ時刻である必要があります。',
            'break2_end.after_or_equal'    => '休憩2の終了は開始より後か同じ時刻である必要があります。',

            'note.max' => '備考は255文字以内で入力してください。',
        ];
    }
}
