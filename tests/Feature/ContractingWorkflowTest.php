<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\ContractTask;
use App\Models\Hotel;
use App\Models\Platform;
use App\Models\Season;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContractingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_contracting_receipt_creates_tasks_and_makes_the_contract_visible_to_the_assigned_agent(): void
    {
        $contracting = User::factory()->create(['role' => 'CONTRACTING']);
        $agent = User::factory()->create(['role' => 'ENTRY_MANAGER']);
        $hotel = Hotel::create(['name' => 'Hotel Contracting Test']);
        $season = Season::create(['name' => 'Winter 2030/2031', 'code' => 'W30', 'start_date' => '2030-11-01', 'end_date' => '2031-04-30']);
        $platform = Platform::create(['name' => 'Test TO', 'code' => 'TEST_TO']);
        $platform->users()->attach($agent);

        $this->actingAs($contracting)
            ->patch(route('contracting.entries.update', [$hotel, $season]), ['purchase_contract_received' => true])
            ->assertSessionHas('success');

        $contract = Contract::where('hotel_id', $hotel->id)->where('season_id', $season->id)->firstOrFail();
        $this->assertTrue($contract->purchase_contract_received);
        $this->assertDatabaseHas('contract_tasks', ['contract_id' => $contract->id, 'platform_id' => $platform->id, 'assigned_user_id' => $agent->id]);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $agent->id, 'notifiable_type' => User::class]);

        $this->actingAs($agent)->get(route('tasks.index'))->assertOk()->assertSee('Hotel Contracting Test');
        $this->getJson(route('notifications.unread'))
            ->assertOk()
            ->assertJsonFragment(['hotel_name' => 'Hotel Contracting Test']);

        $this->actingAs($contracting)
            ->patch(route('contracting.entries.update', [$hotel, $season]), ['purchase_contract_received' => false])
            ->assertSessionHas('success');
        $notification = $agent->notifications()->firstOrFail();
        $this->assertTrue((bool) $notification->data['cancelled']);
        $this->assertSame('Contract cancelled', $notification->data['title']);
    }

    public function test_unchecking_keeps_existing_tasks(): void
    {
        $contracting = User::factory()->create(['role' => 'CONTRACTING']);
        $hotel = Hotel::create(['name' => 'Hotel Keep Tasks']);
        $season = Season::create(['name' => 'Summer 2031', 'code' => 'S31', 'start_date' => '2031-05-01', 'end_date' => '2031-10-31']);
        $platform = Platform::create(['name' => 'Test TO 2', 'code' => 'TEST_TO_2']);
        $contract = Contract::create(['hotel_id' => $hotel->id, 'season_id' => $season->id, 'received_date' => now(), 'purchase_contract_received' => true]);
        ContractTask::create(['contract_id' => $contract->id, 'platform_id' => $platform->id]);

        $this->actingAs($contracting)
            ->patch(route('contracting.entries.update', [$hotel, $season]), ['purchase_contract_received' => false])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('contracts', ['id' => $contract->id, 'purchase_contract_received' => false]);
        $this->assertDatabaseCount('contract_tasks', 1);
    }

    public function test_adding_a_hotel_saves_its_selected_region_for_contracting_filters(): void
    {
        $contracting = User::factory()->create(['role' => 'CONTRACTING']);

        $response = $this->actingAs($contracting)
            ->post(route('contracting.hotels.store'), [
                'name' => 'Nabeul Filter Test Hotel',
                'destination' => 'Nabeul',
                'contract_type' => 'YEAR',
                'start_date' => '2026-11-01',
                'end_date' => '2027-10-31',
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('hotels', ['name' => 'Nabeul Filter Test Hotel', 'destination' => 'Nabeul']);
        $this->get(route('contracting.index', ['hotel' => 'Nabeul Filter Test Hotel', 'region' => 'Nabeul']))
            ->assertOk()
            ->assertSee('Nabeul Filter Test Hotel');
    }

    public function test_contracting_rejects_a_may_period_labelled_as_winter(): void
    {
        $contracting = User::factory()->create(['role' => 'CONTRACTING']);

        $response = $this->actingAs($contracting)
            ->post(route('contracting.hotels.store'), [
                'name' => 'Wrong Winter Validation Hotel',
                'destination' => 'Hammamet',
                'contract_type' => 'WINTER',
                'start_date' => '2027-05-01',
                'end_date' => '2027-10-31',
                'form_context' => 'hotel',
            ])
            ->assertSessionHasErrors('contract_type');

        $this->assertDatabaseMissing('hotels', ['name' => 'Wrong Winter Validation Hotel']);
        $this->actingAs($contracting)
            ->get(route('contracting.index'))
            ->assertOk()
            ->assertSee('Contract type is incorrect')
            ->assertSee('hotel-modal open', false);
    }

    public function test_a_hotel_can_be_added_again_after_its_previous_period_was_deleted(): void
    {
        $contracting = User::factory()->create(['role' => 'CONTRACTING']);
        $hotel = Hotel::create(['name' => 'Readded Hotel', 'destination' => 'Hammamet']);
        Season::create([
            'hotel_id' => $hotel->id,
            'name' => 'Deleted Winter',
            'code' => 'DELETED-W',
            'contract_type' => 'WINTER',
            'start_date' => '2025-11-01',
            'end_date' => '2026-04-30',
            'active' => false,
        ]);

        $this->actingAs($contracting)
            ->post(route('contracting.hotels.store'), [
                'name' => 'Readded Hotel',
                'destination' => 'Hammamet',
                'contract_type' => 'WINTER',
                'start_date' => '2026-11-01',
                'end_date' => '2027-04-30',
                'form_context' => 'hotel',
            ])
            ->assertSessionHas('success');

        $this->assertSame(1, Hotel::where('name', 'Readded Hotel')->count());
        $this->assertDatabaseHas('seasons', ['hotel_id' => $hotel->id, 'contract_type' => 'WINTER', 'active' => true]);
    }

    public function test_a_november_period_labelled_as_summer_keeps_the_add_hotel_modal_open(): void
    {
        $contracting = User::factory()->create(['role' => 'CONTRACTING']);

        $response = $this->actingAs($contracting)
            ->post(route('contracting.hotels.store'), [
                'name' => 'Wrong Summer Validation Hotel',
                'destination' => 'Hammamet',
                'contract_type' => 'SUMMER',
                'start_date' => '2026-11-01',
                'end_date' => '2027-04-30',
                'form_context' => 'hotel',
            ])
            ->assertSessionHasErrors('contract_type');

        $this->assertDatabaseMissing('hotels', ['name' => 'Wrong Summer Validation Hotel']);
        $this->actingAs($contracting)
            ->get(route('contracting.index'))
            ->assertOk()
            ->assertSee('Contract type is incorrect: this period is Winter.')
            ->assertSee('hotel-modal open', false);
    }

    public function test_inactive_periods_are_excluded_from_agent_and_admin_progress(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $agent = User::factory()->create(['role' => 'ENTRY_MANAGER']);
        $hotel = Hotel::create(['name' => 'Current Progress Hotel']);
        $activeSeason = Season::create([
            'hotel_id' => $hotel->id,
            'name' => 'Current Winter',
            'code' => 'CURRENT-W',
            'start_date' => today(),
            'end_date' => today()->addMonth(),
            'active' => true,
        ]);
        $inactiveSeason = Season::create([
            'hotel_id' => $hotel->id,
            'name' => 'Inactive Winter',
            'code' => 'INACTIVE-W',
            'start_date' => today(),
            'end_date' => today()->addMonth(),
            'active' => false,
        ]);
        $platform = Platform::create(['name' => 'Progress Test TO', 'code' => 'PROGRESS_TEST']);
        $platform->users()->attach($agent);
        $activeContract = Contract::create(['hotel_id' => $hotel->id, 'season_id' => $activeSeason->id, 'purchase_contract_received' => true, 'received_date' => now()]);
        $inactiveContract = Contract::create(['hotel_id' => $hotel->id, 'season_id' => $inactiveSeason->id, 'purchase_contract_received' => true, 'received_date' => now()]);
        ContractTask::create(['contract_id' => $activeContract->id, 'platform_id' => $platform->id, 'assigned_user_id' => $agent->id]);
        ContractTask::create(['contract_id' => $inactiveContract->id, 'platform_id' => $platform->id, 'assigned_user_id' => $agent->id]);

        $this->actingAs($agent)->get(route('tasks.index'))
            ->assertOk()
            ->assertSee('Current Winter')
            ->assertDontSee('Inactive Winter');
        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Current Winter')
            ->assertDontSee('Inactive Winter');
    }

    public function test_contracting_can_activate_only_the_selected_tos_for_a_contract(): void
    {
        $contracting = User::factory()->create(['role' => 'CONTRACTING']);
        $hotel = Hotel::create(['name' => 'Aurum Only Hotel']);
        $season = Season::create([
            'hotel_id' => $hotel->id,
            'name' => 'Current Summer',
            'code' => 'CURRENT-S',
            'start_date' => today(),
            'end_date' => today()->addMonth(),
            'active' => true,
        ]);
        $aurum = Platform::create(['name' => 'Aurum Test', 'code' => 'AURUM_TEST', 'label' => 'AURUM']);
        $dna = Platform::create(['name' => 'DNA Test', 'code' => 'DNA_TEST', 'label' => 'DNA']);
        $contract = Contract::create(['hotel_id' => $hotel->id, 'season_id' => $season->id, 'purchase_contract_received' => true, 'received_date' => now()]);
        ContractTask::create(['contract_id' => $contract->id, 'platform_id' => $aurum->id]);
        ContractTask::create(['contract_id' => $contract->id, 'platform_id' => $dna->id]);

        $this->actingAs($contracting)
            ->patch(route('contracting.to-configuration.update', $aurum), ['contract_ids' => [$contract->id]])
            ->assertRedirect(route('contracting.to-configuration', ['platform' => $aurum->id]));

        $this->assertDatabaseHas('contract_tasks', ['contract_id' => $contract->id, 'platform_id' => $aurum->id, 'is_active' => true]);
        $this->actingAs($contracting)
            ->patch(route('contracting.to-configuration.update', $dna), ['contract_ids' => []])
            ->assertRedirect(route('contracting.to-configuration', ['platform' => $dna->id]));
        $this->assertDatabaseHas('contract_tasks', ['contract_id' => $contract->id, 'platform_id' => $dna->id, 'is_active' => false]);
    }

    public function test_contracting_and_agent_can_download_excel_reports(): void
    {
        $contracting = User::factory()->create(['role' => 'CONTRACTING']);
        $agent = User::factory()->create(['role' => 'ENTRY_MANAGER']);
        $hotel = Hotel::create(['name' => 'Excel Export Hotel']);
        $season = Season::create([
            'hotel_id' => $hotel->id,
            'name' => 'Excel Export Winter',
            'code' => 'EXCEL-W',
            'contract_type' => 'WINTER',
            'start_date' => today(),
            'end_date' => today()->addMonth(),
            'active' => true,
        ]);
        $contract = Contract::create(['hotel_id' => $hotel->id, 'season_id' => $season->id, 'purchase_contract_received' => true, 'received_date' => now()]);
        $platform = Platform::create(['name' => 'Excel TO', 'code' => 'EXCEL_TO', 'label' => 'EXCEL']);
        ContractTask::create(['contract_id' => $contract->id, 'platform_id' => $platform->id, 'assigned_user_id' => $agent->id]);

        $this->actingAs($contracting)
            ->get(route('contracting.export', ['receipt_status' => 'received']))
            ->assertOk()
            ->assertDownload('contracting-received-'.now()->format('Y-m-d').'.xlsx');
        $this->actingAs($agent)
            ->get(route('tasks.export'))
            ->assertOk()
            ->assertDownload('my-task-status-'.now()->format('Y-m-d').'.xlsx');
    }
}
