<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('user_id')->constrained('users');
            $table->foreignId('barcode_id')->nullable()->constrained('barcodes');
            $table->date('date')->nullable();
            $table->time('time_in')->nullable(); // check-in time
            $table->time('time_out')->nullable(); // check-out time
            $table->foreignId('shift_id')->nullable()->constrained('shifts');
            $table->double('latitude')->nullable(); // check-in latitude
            $table->double('longitude')->nullable(); // check-in longitude
            $table->enum('status', [
                'present', // hadir
                'late', // terlambat
                'excused', // izin
                'sick', // sakit
                'absent' // tidak hadir
            ])->default('absent');
            $table->string('note')->nullable(); // keterangan
            $table->string('attachment')->nullable(); // lampiran
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
