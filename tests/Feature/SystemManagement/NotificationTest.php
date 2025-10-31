<?php

namespace Tests\Feature\SystemManagement;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_notification(): void
    {
        $user = User::factory()->create();

        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => 'appointment_reminder',
            'title' => 'Appointment Reminder',
            'message' => 'You have an appointment tomorrow at 2:00 PM',
            'data' => json_encode(['appointment_id' => 123]),
            'is_read' => false,
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'type' => 'appointment_reminder',
            'title' => 'Appointment Reminder',
            'message' => 'You have an appointment tomorrow at 2:00 PM',
            'is_read' => false,
        ]);

        $this->assertEquals('appointment_reminder', $notification->type);
        $this->assertFalse($notification->is_read);
    }

    public function test_notification_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $notification = Notification::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $notification->user);
        $this->assertEquals($user->id, $notification->user->id);
    }

    public function test_notification_data_accessor(): void
    {
        $data = ['appointment_id' => 123, 'clinic_name' => 'Pet Care Clinic'];
        $notification = Notification::factory()->create([
            'data' => json_encode($data),
        ]);

        $this->assertEquals($data, $notification->data);
    }

    public function test_notification_data_mutator(): void
    {
        $notification = Notification::factory()->create();
        $data = ['key' => 'value', 'number' => 42];
        
        $notification->data = $data;
        $notification->save();

        $this->assertEquals($data, $notification->fresh()->data);
    }

    public function test_notification_type_display(): void
    {
        $notification = Notification::factory()->create(['type' => 'appointment_reminder']);
        $this->assertEquals('Appointment Reminder', $notification->type_display);

        $notification->type = 'vaccination_due';
        $this->assertEquals('Vaccination Due', $notification->type_display);

        $notification->type = 'clinic_promotion';
        $this->assertEquals('Clinic Promotion', $notification->type_display);
    }

    public function test_notification_formatted_created_at(): void
    {
        $notification = Notification::factory()->create([
            'created_at' => now()->subHours(2),
        ]);

        $formatted = $notification->formatted_created_at;
        $this->assertStringContains('2 hours ago', $formatted);
    }

    public function test_notification_mark_as_read(): void
    {
        $notification = Notification::factory()->create(['is_read' => false]);
        
        $this->assertFalse($notification->is_read);
        
        $notification->markAsRead();
        
        $this->assertTrue($notification->fresh()->is_read);
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_notification_mark_as_unread(): void
    {
        $notification = Notification::factory()->create([
            'is_read' => true,
            'read_at' => now(),
        ]);
        
        $this->assertTrue($notification->is_read);
        
        $notification->markAsUnread();
        
        $this->assertFalse($notification->fresh()->is_read);
        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_can_filter_unread_notifications(): void
    {
        $user = User::factory()->create();
        
        $readNotification = Notification::factory()->create([
            'user_id' => $user->id,
            'is_read' => true,
        ]);
        
        $unreadNotification = Notification::factory()->create([
            'user_id' => $user->id,
            'is_read' => false,
        ]);

        $unreadNotifications = Notification::unread()->get();
        $this->assertCount(1, $unreadNotifications);
        $this->assertEquals($unreadNotification->id, $unreadNotifications->first()->id);
    }

    public function test_can_filter_read_notifications(): void
    {
        $user = User::factory()->create();
        
        $readNotification = Notification::factory()->create([
            'user_id' => $user->id,
            'is_read' => true,
        ]);
        
        $unreadNotification = Notification::factory()->create([
            'user_id' => $user->id,
            'is_read' => false,
        ]);

        $readNotifications = Notification::read()->get();
        $this->assertCount(1, $readNotifications);
        $this->assertEquals($readNotification->id, $readNotifications->first()->id);
    }

    public function test_can_filter_notifications_by_type(): void
    {
        $appointmentNotification = Notification::factory()->create(['type' => 'appointment_reminder']);
        $vaccinationNotification = Notification::factory()->create(['type' => 'vaccination_due']);

        $appointmentNotifications = Notification::byType('appointment_reminder')->get();
        $this->assertCount(1, $appointmentNotifications);
        $this->assertEquals('appointment_reminder', $appointmentNotifications->first()->type);
    }

    public function test_can_filter_recent_notifications(): void
    {
        $recentNotification = Notification::factory()->create([
            'created_at' => now()->subHours(2),
        ]);
        
        $oldNotification = Notification::factory()->create([
            'created_at' => now()->subDays(8),
        ]);

        $recentNotifications = Notification::recent()->get();
        
        // Recent scope should include notifications from last 7 days
        $this->assertTrue($recentNotifications->contains($recentNotification));
        $this->assertFalse($recentNotifications->contains($oldNotification));
    }

    public function test_notification_is_urgent(): void
    {
        $urgentNotification = Notification::factory()->create([
            'type' => 'emergency_alert',
        ]);
        $this->assertTrue($urgentNotification->is_urgent);

        $regularNotification = Notification::factory()->create([
            'type' => 'appointment_reminder',
        ]);
        $this->assertFalse($regularNotification->is_urgent);
    }

    public function test_notification_icon(): void
    {
        $appointmentNotification = Notification::factory()->create(['type' => 'appointment_reminder']);
        $this->assertEquals('calendar', $appointmentNotification->icon);

        $vaccinationNotification = Notification::factory()->create(['type' => 'vaccination_due']);
        $this->assertEquals('medical', $vaccinationNotification->icon);

        $promotionNotification = Notification::factory()->create(['type' => 'clinic_promotion']);
        $this->assertEquals('star', $promotionNotification->icon);
    }

    public function test_user_has_many_notifications(): void
    {
        $user = User::factory()->create();
        $notification1 = Notification::factory()->create(['user_id' => $user->id]);
        $notification2 = Notification::factory()->create(['user_id' => $user->id]);

        $this->assertCount(2, $user->notifications);
        $this->assertTrue($user->notifications->contains($notification1));
        $this->assertTrue($user->notifications->contains($notification2));
    }

    public function test_user_unread_notifications_count(): void
    {
        $user = User::factory()->create();
        
        Notification::factory()->count(3)->create([
            'user_id' => $user->id,
            'is_read' => false,
        ]);
        
        Notification::factory()->count(2)->create([
            'user_id' => $user->id,
            'is_read' => true,
        ]);

        $this->assertEquals(3, $user->unread_notifications_count);
    }

    public function test_notification_action_url(): void
    {
        $appointmentNotification = Notification::factory()->create([
            'type' => 'appointment_reminder',
            'data' => json_encode(['appointment_id' => 123]),
        ]);

        $actionUrl = $appointmentNotification->action_url;
        $this->assertStringContains('/appointments/123', $actionUrl);
    }
}