<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorrectionRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('correction_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // 出勤・退勤
            $table->time('requested_start_time')->nullable();
            $table->time('requested_end_time')->nullable();

            // 休憩1
            $table->time('requested_break_start')->nullable();
            $table->time('requested_break_end')->nullable();

            // 休憩2 ←追加
            $table->time('requested_break2_start')->nullable();
            $table->time('requested_break2_end')->nullable();

            // 備考
            $table->text('requested_note')->nullable();

            // ステータスと承認日時
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('correction_requests');
    }
}
