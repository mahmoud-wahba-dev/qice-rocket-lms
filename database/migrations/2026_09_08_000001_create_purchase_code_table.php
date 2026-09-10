<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * The RocketLMS core (ionCube-encoded PurchaseCode model) reads/writes the
     * `purchase_code` table, but no stock migration creates it — production
     * databases got it via SQL-dump import. This migration makes fresh
     * `php artisan migrate` installs work (local dev, CI).
     *
     * Columns derived from the model's fillable: code, product_type, license_type.
     * `purchase_code` column kept as alias for compatibility.
     */
    public function up()
    {
        if (Schema::hasTable('purchase_code')) {
            return;
        }

        Schema::create('purchase_code', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('code')->nullable();
            $table->string('purchase_code')->nullable();
            $table->string('product_type', 50)->default('main');
            $table->string('license_type', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('purchase_code');
    }
};
