<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('pautan_fotostat',function(Blueprint $t){
   $t->id();$t->foreignId('sewaan_id')->unique()->constrained('butiran_anggaran')->restrictOnDelete();
   $t->foreignId('meter_id')->unique()->constrained('butiran_anggaran')->restrictOnDelete();$t->timestamps();
  });
 }
 public function down(): void {Schema::dropIfExists('pautan_fotostat');}
};
