<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Canonical person spine plus the applicant profile extension.
 *
 * Purpose: an applicant, a student and (later) an alumnus are the same human being. `people` is the
 * immutable identity anchor they all reference, so student conversion never has to copy identity data.
 *
 * Identity numbers are stored encrypted with a separate keyed hash for duplicate detection: the hash is
 * indexable and the ciphertext is not, and neither leaks the number to a database reader.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('people', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('institution_id')->nullable()->index();
            $t->string('given_name', 120);
            $t->string('middle_name', 120)->nullable();
            $t->string('family_name', 120);
            $t->string('preferred_name', 120)->nullable();
            $t->jsonb('previous_names')->default('[]');
            // M | F | X | UNDISCLOSED — self-declared, never inferred.
            $t->string('gender', 20)->nullable();
            $t->date('date_of_birth')->nullable();
            $t->string('place_of_birth', 190)->nullable();
            $t->string('nationality_code', 2)->nullable();
            $t->string('country_of_residence_code', 2)->nullable();
            $t->string('county_code', 10)->nullable();
            // national_id | birth_certificate | passport | alien_id | military_id
            $t->string('identity_type', 30)->nullable();
            $t->text('identity_number_encrypted')->nullable();
            $t->string('identity_number_hash', 64)->nullable()->index();
            $t->string('identity_number_masked', 40)->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $t->unsignedInteger('lock_version')->default(1);
            $t->timestampsTz();
            $t->softDeletesTz();
            $t->index(['family_name', 'given_name']);
        });

        Schema::create('contact_points', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->foreignUuid('person_id')->constrained('people')->cascadeOnDelete();
            // email | phone | whatsapp
            $t->string('contact_type', 20);
            $t->string('raw_value', 190);
            // E.164 for phones, lower-cased trimmed address for email.
            $t->string('normalised_value', 190);
            $t->string('country_code', 5)->nullable();
            $t->boolean('is_primary')->default(false);
            $t->timestampTz('verified_at')->nullable();
            $t->timestampsTz();
            $t->unique(['person_id', 'contact_type', 'normalised_value']);
            $t->index(['contact_type', 'normalised_value']);
        });

        Schema::create('addresses', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->foreignUuid('person_id')->constrained('people')->cascadeOnDelete();
            // postal | physical
            $t->string('address_type', 20);
            $t->string('line1', 190)->nullable();
            $t->string('line2', 190)->nullable();
            $t->string('town', 120)->nullable();
            $t->string('county_code', 10)->nullable();
            $t->string('postal_code', 20)->nullable();
            $t->string('country_code', 2)->default('KE');
            $t->boolean('is_primary')->default(false);
            $t->timestampsTz();
            $t->unique(['person_id', 'address_type']);
        });

        Schema::table('applicant_profiles', function (Blueprint $t): void {
            $t->uuid('person_id')->nullable()->index();
            $t->uuid('institution_id')->nullable()->index();
            $t->string('phone_country_code', 5)->nullable();
            $t->string('phone_e164', 20)->nullable()->index();
            $t->string('referral_code', 60)->nullable();
            // organic | agent | school_visit | social | referral | radio | exhibition | walk_in
            $t->string('acquisition_source', 40)->nullable();
            $t->jsonb('contact_preferences')->default('{"email":true,"sms":true,"whatsapp":false}');
            $t->boolean('marketing_consent')->default(false);
            $t->string('terms_version', 40)->nullable();
            $t->string('privacy_version', 40)->nullable();
            $t->string('cookie_version', 40)->nullable();
            // Restricted classification: never returned by default projections or exports.
            $t->string('support_category', 60)->nullable();
            $t->string('emergency_contact_name', 190)->nullable();
            $t->string('emergency_contact_relationship', 60)->nullable();
            $t->string('emergency_contact_phone', 20)->nullable();
            $t->string('emergency_contact_email', 190)->nullable();
            $t->unsignedInteger('lock_version')->default(1);
            $t->timestampTz('archived_at')->nullable();
            $t->softDeletesTz();
        });
    }

    public function down(): void
    {
        Schema::table('applicant_profiles', function (Blueprint $t): void {
            $t->dropColumn([
                'person_id', 'institution_id', 'phone_country_code', 'phone_e164', 'referral_code',
                'acquisition_source', 'contact_preferences', 'marketing_consent', 'terms_version',
                'privacy_version', 'cookie_version', 'support_category', 'emergency_contact_name',
                'emergency_contact_relationship', 'emergency_contact_phone', 'emergency_contact_email',
                'lock_version', 'archived_at', 'deleted_at',
            ]);
        });
        foreach (['addresses', 'contact_points', 'people'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
