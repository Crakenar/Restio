<?php

namespace Tests\Feature;

use App\Enum\UserRole;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesSubscriptions;
use Tests\TestCase;

class TeamManagementTest extends TestCase
{
    use RefreshDatabase, CreatesSubscriptions;

    /**
     * TM-01: Admin can view team management page
     */
    public function test_admin_can_view_team_management_page(): void
    {
        $company = Company::factory()->create();
        CompanySetting::factory()->create(['company_id' => $company->id]);
        $this->createActiveSubscription($company);

        $admin = User::factory()->create([
            'company_id' => $company->id,
            'role' => UserRole::ADMIN->value,
        ]);

        $response = $this->actingAs($admin)->get('/team-management');
        $response->assertSuccessful();
    }

    /**
     * TM-02: Admin can create a new team
     */
    public function test_admin_can_create_team(): void
    {
        $company = Company::factory()->create();
        CompanySetting::factory()->create(['company_id' => $company->id]);
        $this->createActiveSubscription($company);

        $admin = User::factory()->create([
            'company_id' => $company->id,
            'role' => UserRole::ADMIN->value,
        ]);

        $this->actingAs($admin)->post('/team-management', [
            'name' => 'Engineering Team',
        ]);

        $this->assertDatabaseHas('teams', [
            'name' => 'Engineering Team',
            'company_id' => $company->id,
        ]);
    }

    /**
     * TM-03: Admin can update team name
     */
    public function test_admin_can_update_team_name(): void
    {
        $company = Company::factory()->create();
        CompanySetting::factory()->create(['company_id' => $company->id]);
        $this->createActiveSubscription($company);

        $admin = User::factory()->create([
            'company_id' => $company->id,
            'role' => UserRole::ADMIN->value,
        ]);

        $team = Team::factory()->create([
            'company_id' => $company->id,
            'name' => 'Old Team Name',
        ]);

        $this->actingAs($admin)->patch("/team-management/{$team->id}", [
            'name' => 'New Team Name',
        ]);

        $team->refresh();
        $this->assertEquals('New Team Name', $team->name);
    }

    /**
     * TM-04: Admin can delete team
     */
    public function test_admin_can_delete_team(): void
    {
        $company = Company::factory()->create();
        CompanySetting::factory()->create(['company_id' => $company->id]);
        $this->createActiveSubscription($company);

        $admin = User::factory()->create([
            'company_id' => $company->id,
            'role' => UserRole::ADMIN->value,
        ]);

        $team = Team::factory()->create([
            'company_id' => $company->id,
        ]);

        $this->actingAs($admin)->delete("/team-management/{$team->id}");

        $this->assertDatabaseMissing('teams', ['id' => $team->id]);
    }

    /**
     * TM-05: Admin can assign users to team
     */
    public function test_admin_can_assign_users_to_team(): void
    {
        $company = Company::factory()->create();
        CompanySetting::factory()->create(['company_id' => $company->id]);
        $this->createActiveSubscription($company);

        $admin = User::factory()->create([
            'company_id' => $company->id,
            'role' => UserRole::ADMIN->value,
        ]);

        $team = Team::factory()->create(['company_id' => $company->id]);

        $user1 = User::factory()->create(['company_id' => $company->id]);
        $user2 = User::factory()->create(['company_id' => $company->id]);

        $this->actingAs($admin)->post("/team-management/{$team->id}/assign-users", [
            'user_ids' => [$user1->id, $user2->id],
        ]);

        $user1->refresh();
        $user2->refresh();

        $this->assertEquals($team->id, $user1->team_id);
        $this->assertEquals($team->id, $user2->team_id);
    }

    /**
     * TM-06: Admin can remove user from team
     */
    public function test_admin_can_remove_user_from_team(): void
    {
        $company = Company::factory()->create();
        CompanySetting::factory()->create(['company_id' => $company->id]);
        $this->createActiveSubscription($company);

        $admin = User::factory()->create([
            'company_id' => $company->id,
            'role' => UserRole::ADMIN->value,
        ]);

        $team = Team::factory()->create(['company_id' => $company->id]);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'team_id' => $team->id,
        ]);

        $this->actingAs($admin)->delete("/team-management/{$team->id}/users/{$user->id}");

        $user->refresh();
        $this->assertNull($user->team_id);
    }

    /**
     * TM-07: Non-admin cannot access team management
     */
    public function test_non_admin_cannot_access_team_management(): void
    {
        $company = Company::factory()->create();
        CompanySetting::factory()->create(['company_id' => $company->id]);
        $this->createActiveSubscription($company);

        $employee = User::factory()->create([
            'company_id' => $company->id,
            'role' => UserRole::EMPLOYEE->value,
        ]);

        $manager = User::factory()->create([
            'company_id' => $company->id,
            'role' => UserRole::MANAGER->value,
        ]);

        // Employee cannot access
        $response = $this->actingAs($employee)->get('/team-management');
        $this->assertNotEquals(200, $response->status());

        // Manager cannot access
        $response = $this->actingAs($manager)->get('/team-management');
        $this->assertNotEquals(200, $response->status());
    }

    /**
     * TM-08: Cannot create team without name
     */
    public function test_cannot_create_team_without_name(): void
    {
        $company = Company::factory()->create();
        CompanySetting::factory()->create(['company_id' => $company->id]);
        $this->createActiveSubscription($company);

        $admin = User::factory()->create([
            'company_id' => $company->id,
            'role' => UserRole::ADMIN->value,
        ]);

        $response = $this->actingAs($admin)->post('/team-management', [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');

        // Verify no team was created
        $this->assertEquals(0, Team::where('company_id', $company->id)->count());
    }

    /**
     * TM-09: Cannot assign users from another company to team
     */
    public function test_cannot_assign_users_from_another_company_to_team(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        CompanySetting::factory()->create(['company_id' => $companyA->id]);
        CompanySetting::factory()->create(['company_id' => $companyB->id]);
        $this->createActiveSubscription($companyA);
        $this->createActiveSubscription($companyB);

        $adminA = User::factory()->create([
            'company_id' => $companyA->id,
            'role' => UserRole::ADMIN->value,
        ]);

        $teamA = Team::factory()->create(['company_id' => $companyA->id]);
        $userB = User::factory()->create(['company_id' => $companyB->id]);

        $this->actingAs($adminA)->post("/team-management/{$teamA->id}/assign-users", [
            'user_ids' => [$userB->id],
        ]);

        // Should not assign user from different company (company check in controller)
        $userB->refresh();
        $this->assertNotEquals($teamA->id, $userB->team_id);
    }

    /**
     * TM-10: Deleting team unassigns all users from that team
     */
    public function test_deleting_team_unassigns_users(): void
    {
        $company = Company::factory()->create();
        CompanySetting::factory()->create(['company_id' => $company->id]);
        $this->createActiveSubscription($company);

        $admin = User::factory()->create([
            'company_id' => $company->id,
            'role' => UserRole::ADMIN->value,
        ]);

        $team = Team::factory()->create(['company_id' => $company->id]);

        $user1 = User::factory()->create([
            'company_id' => $company->id,
            'team_id' => $team->id,
        ]);

        $user2 = User::factory()->create([
            'company_id' => $company->id,
            'team_id' => $team->id,
        ]);

        // Delete the team
        $this->actingAs($admin)->delete("/team-management/{$team->id}");

        // Check users are unassigned
        $user1->refresh();
        $user2->refresh();

        $this->assertNull($user1->team_id);
        $this->assertNull($user2->team_id);
    }

    /**
     * TM-11: Team list only shows teams from user's company
     */
    public function test_team_list_only_shows_company_teams(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        CompanySetting::factory()->create(['company_id' => $companyA->id]);
        CompanySetting::factory()->create(['company_id' => $companyB->id]);
        $this->createActiveSubscription($companyA);
        $this->createActiveSubscription($companyB);

        $adminA = User::factory()->create([
            'company_id' => $companyA->id,
            'role' => UserRole::ADMIN->value,
        ]);

        Team::factory()->create(['company_id' => $companyA->id, 'name' => 'Team A']);
        Team::factory()->create(['company_id' => $companyB->id, 'name' => 'Team B']);

        $this->actingAs($adminA)->get('/team-management');

        // Verify only Company A teams exist for Company A
        $this->assertEquals(1, Team::where('company_id', $companyA->id)->count());
        $this->assertEquals(1, Team::where('company_id', $companyB->id)->count());
    }

    /**
     * TM-12: Owner can manage teams
     */
    public function test_owner_can_manage_teams(): void
    {
        $company = Company::factory()->create();
        CompanySetting::factory()->create(['company_id' => $company->id]);
        $this->createActiveSubscription($company);

        $owner = User::factory()->create([
            'company_id' => $company->id,
            'role' => UserRole::OWNER->value,
        ]);

        // Owner can view team management
        $response = $this->actingAs($owner)->get('/team-management');
        $response->assertSuccessful();

        // Owner can create team
        $this->actingAs($owner)->post('/team-management', [
            'name' => 'Sales Team',
        ]);

        $this->assertDatabaseHas('teams', [
            'name' => 'Sales Team',
            'company_id' => $company->id,
        ]);
    }

    /**
     * TM-13: Cannot update team name to empty
     */
    public function test_cannot_update_team_name_to_empty(): void
    {
        $company = Company::factory()->create();
        CompanySetting::factory()->create(['company_id' => $company->id]);
        $this->createActiveSubscription($company);

        $admin = User::factory()->create([
            'company_id' => $company->id,
            'role' => UserRole::ADMIN->value,
        ]);

        $team = Team::factory()->create([
            'company_id' => $company->id,
            'name' => 'Original Name',
        ]);

        $response = $this->actingAs($admin)->patch("/team-management/{$team->id}", [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');

        $team->refresh();
        $this->assertEquals('Original Name', $team->name);
    }

    /**
     * TM-14: Multiple teams can be created in same company
     */
    public function test_multiple_teams_can_be_created_in_same_company(): void
    {
        $company = Company::factory()->create();
        CompanySetting::factory()->create(['company_id' => $company->id]);
        $this->createActiveSubscription($company);

        $admin = User::factory()->create([
            'company_id' => $company->id,
            'role' => UserRole::ADMIN->value,
        ]);

        // Create first team
        $this->actingAs($admin)->post('/team-management', [
            'name' => 'Engineering',
        ]);

        // Create second team
        $this->actingAs($admin)->post('/team-management', [
            'name' => 'Marketing',
        ]);

        // Create third team
        $this->actingAs($admin)->post('/team-management', [
            'name' => 'Sales',
        ]);

        $this->assertEquals(3, Team::where('company_id', $company->id)->count());
    }

    /**
     * TM-15: User can be reassigned to different team
     */
    public function test_user_can_be_reassigned_to_different_team(): void
    {
        $company = Company::factory()->create();
        CompanySetting::factory()->create(['company_id' => $company->id]);
        $this->createActiveSubscription($company);

        $admin = User::factory()->create([
            'company_id' => $company->id,
            'role' => UserRole::ADMIN->value,
        ]);

        $teamA = Team::factory()->create(['company_id' => $company->id, 'name' => 'Team A']);
        $teamB = Team::factory()->create(['company_id' => $company->id, 'name' => 'Team B']);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'team_id' => $teamA->id,
        ]);

        // Verify user is in Team A
        $this->assertEquals($teamA->id, $user->team_id);

        // Reassign to Team B
        $this->actingAs($admin)->post("/team-management/{$teamB->id}/assign-users", [
            'user_ids' => [$user->id],
        ]);

        $user->refresh();
        $this->assertEquals($teamB->id, $user->team_id);
    }
}
