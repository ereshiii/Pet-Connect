<?php

namespace Tests\Feature\SystemManagement;

use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_system_setting(): void
    {
        $setting = SystemSetting::create([
            'key' => 'app_name',
            'value' => 'PetConnect',
            'type' => 'string',
            'description' => 'Application name displayed in the header',
            'category' => 'general',
            'is_public' => true,
        ]);

        $this->assertDatabaseHas('system_settings', [
            'key' => 'app_name',
            'value' => 'PetConnect',
            'type' => 'string',
            'category' => 'general',
            'is_public' => true,
        ]);

        $this->assertEquals('app_name', $setting->key);
        $this->assertEquals('PetConnect', $setting->value);
    }

    public function test_can_get_setting_value_by_key(): void
    {
        SystemSetting::create([
            'key' => 'max_appointments_per_day',
            'value' => '10',
            'type' => 'integer',
        ]);

        $value = SystemSetting::getValue('max_appointments_per_day');
        $this->assertEquals(10, $value);
    }

    public function test_can_get_setting_with_default_value(): void
    {
        $value = SystemSetting::getValue('non_existent_key', 'default_value');
        $this->assertEquals('default_value', $value);
    }

    public function test_can_set_setting_value(): void
    {
        SystemSetting::setValue('test_key', 'test_value', 'string');
        
        $this->assertDatabaseHas('system_settings', [
            'key' => 'test_key',
            'value' => 'test_value',
            'type' => 'string',
        ]);

        // Update existing setting
        SystemSetting::setValue('test_key', 'updated_value', 'string');
        
        $this->assertDatabaseHas('system_settings', [
            'key' => 'test_key',
            'value' => 'updated_value',
        ]);

        $this->assertDatabaseMissing('system_settings', [
            'key' => 'test_key',
            'value' => 'test_value',
        ]);
    }

    public function test_setting_value_casting(): void
    {
        // String setting
        $stringSetting = SystemSetting::create([
            'key' => 'string_setting',
            'value' => 'hello world',
            'type' => 'string',
        ]);
        $this->assertEquals('hello world', $stringSetting->typed_value);

        // Integer setting
        $integerSetting = SystemSetting::create([
            'key' => 'integer_setting',
            'value' => '42',
            'type' => 'integer',
        ]);
        $this->assertEquals(42, $integerSetting->typed_value);
        $this->assertIsInt($integerSetting->typed_value);

        // Boolean setting
        $booleanSetting = SystemSetting::create([
            'key' => 'boolean_setting',
            'value' => 'true',
            'type' => 'boolean',
        ]);
        $this->assertTrue($booleanSetting->typed_value);
        $this->assertIsBool($booleanSetting->typed_value);

        // Array setting
        $arraySetting = SystemSetting::create([
            'key' => 'array_setting',
            'value' => json_encode(['item1', 'item2', 'item3']),
            'type' => 'array',
        ]);
        $this->assertEquals(['item1', 'item2', 'item3'], $arraySetting->typed_value);
        $this->assertIsArray($arraySetting->typed_value);

        // JSON setting
        $jsonSetting = SystemSetting::create([
            'key' => 'json_setting',
            'value' => json_encode(['key' => 'value', 'number' => 123]),
            'type' => 'json',
        ]);
        $this->assertEquals(['key' => 'value', 'number' => 123], $jsonSetting->typed_value);
    }

    public function test_can_filter_settings_by_category(): void
    {
        $generalSetting = SystemSetting::create([
            'key' => 'app_name',
            'value' => 'PetConnect',
            'category' => 'general',
        ]);

        $emailSetting = SystemSetting::create([
            'key' => 'smtp_host',
            'value' => 'smtp.gmail.com',
            'category' => 'email',
        ]);

        $generalSettings = SystemSetting::byCategory('general')->get();
        $this->assertCount(1, $generalSettings);
        $this->assertEquals('app_name', $generalSettings->first()->key);
    }

    public function test_can_filter_public_settings(): void
    {
        $publicSetting = SystemSetting::create([
            'key' => 'app_name',
            'value' => 'PetConnect',
            'is_public' => true,
        ]);

        $privateSetting = SystemSetting::create([
            'key' => 'api_secret',
            'value' => 'secret123',
            'is_public' => false,
        ]);

        $publicSettings = SystemSetting::public()->get();
        $this->assertCount(1, $publicSettings);
        $this->assertEquals('app_name', $publicSettings->first()->key);
    }

    public function test_can_search_settings(): void
    {
        $setting1 = SystemSetting::create([
            'key' => 'app_name',
            'value' => 'PetConnect',
            'description' => 'Application name',
        ]);

        $setting2 = SystemSetting::create([
            'key' => 'max_file_size',
            'value' => '10MB',
            'description' => 'Maximum upload file size',
        ]);

        $results = SystemSetting::search('app')->get();
        $this->assertCount(1, $results);
        $this->assertEquals('app_name', $results->first()->key);

        $results = SystemSetting::search('size')->get();
        $this->assertCount(1, $results);
        $this->assertEquals('max_file_size', $results->first()->key);
    }

    public function test_setting_type_display(): void
    {
        $setting = SystemSetting::create(['type' => 'string']);
        $this->assertEquals('String', $setting->type_display);

        $setting->type = 'integer';
        $this->assertEquals('Integer', $setting->type_display);

        $setting->type = 'boolean';
        $this->assertEquals('Boolean', $setting->type_display);
    }

    public function test_setting_is_editable(): void
    {
        $editableSetting = SystemSetting::create([
            'key' => 'app_name',
            'value' => 'PetConnect',
        ]);
        $this->assertTrue($editableSetting->is_editable);

        // System critical settings should not be editable
        $systemSetting = SystemSetting::create([
            'key' => 'system_version',
            'value' => '1.0.0',
            'category' => 'system',
        ]);
        $this->assertFalse($systemSetting->is_editable);
    }

    public function test_setting_validation_rules(): void
    {
        $setting = SystemSetting::create([
            'key' => 'max_appointments',
            'type' => 'integer',
            'validation_rules' => 'required|integer|min:1|max:100',
        ]);

        $rules = $setting->validation_rules_array;
        $this->assertIsArray($rules);
        $this->assertContains('required', $rules);
        $this->assertContains('integer', $rules);
        $this->assertContains('min:1', $rules);
        $this->assertContains('max:100', $rules);
    }

    public function test_can_get_all_settings_as_config(): void
    {
        SystemSetting::create(['key' => 'app.name', 'value' => 'PetConnect']);
        SystemSetting::create(['key' => 'app.version', 'value' => '1.0.0']);
        SystemSetting::create(['key' => 'mail.host', 'value' => 'smtp.gmail.com']);

        $config = SystemSetting::getAllAsConfig();
        
        $this->assertIsArray($config);
        $this->assertEquals('PetConnect', $config['app.name']);
        $this->assertEquals('1.0.0', $config['app.version']);
        $this->assertEquals('smtp.gmail.com', $config['mail.host']);
    }

    public function test_can_bulk_update_settings(): void
    {
        SystemSetting::create(['key' => 'setting1', 'value' => 'old_value1']);
        SystemSetting::create(['key' => 'setting2', 'value' => 'old_value2']);

        $updates = [
            'setting1' => 'new_value1',
            'setting2' => 'new_value2',
            'setting3' => 'new_value3', // New setting
        ];

        SystemSetting::bulkUpdate($updates);

        $this->assertDatabaseHas('system_settings', [
            'key' => 'setting1',
            'value' => 'new_value1',
        ]);

        $this->assertDatabaseHas('system_settings', [
            'key' => 'setting2',
            'value' => 'new_value2',
        ]);

        $this->assertDatabaseHas('system_settings', [
            'key' => 'setting3',
            'value' => 'new_value3',
        ]);
    }
}