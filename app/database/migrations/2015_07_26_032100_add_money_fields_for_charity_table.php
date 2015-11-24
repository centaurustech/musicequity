<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddMoneyFieldsForCharityTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('charity', function(Blueprint $table)
		{
				DB::statement('ALTER TABLE charity MODIFY COLUMN donated NUMERIC(5,2)');
				DB::statement('ALTER TABLE charity MODIFY COLUMN goal NUMERIC(5,2)');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('charity', function(Blueprint $table)
		{

		});
	}

}
