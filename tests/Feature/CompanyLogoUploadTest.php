<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CompanyLogoUploadTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::firstOrCreate(['slug' => 'super_admin'], ['name' => 'Super Admin', 'is_system' => true]);

        $company = Company::create([
            'company_code'    => 'BASE',
            'name'            => 'Base Company',
            'contact_primary' => '9999999999',
            'address'         => 'Base Address',
        ]);

        $this->superAdmin = User::create([
            'company_id' => $company->id,
            'role_id'    => $role->id,
            'user_code'  => 'BASE/USR-1001',
            'name'       => 'Super Admin',
            'email'      => 'super@erp.com',
            'password'   => Hash::make('password'),
            'role'       => 'super_admin',
            'is_active'  => true,
        ]);
    }

    public function test_public_storage_is_a_real_directory_and_not_a_symlink(): void
    {
        $storagePath = public_path('storage');
        $this->assertDirectoryExists($storagePath);
        $this->assertFalse(is_link($storagePath), 'public/storage should be a physical directory, not a symbolic link.');
    }

    public function test_uploading_logo_stores_directly_in_public_storage_folder(): void
    {
        $this->actingAs($this->superAdmin);

        $fakeLogo = UploadedFile::fake()->image('test_logo.png', 200, 200);

        $response = $this->post(route('admin.companies.store'), [
            'name'            => 'Horizon Estates',
            'company_code'    => 'HRZ',
            'contact_primary' => '9876543210',
            'address'         => 'Guwahati, Assam',
            'logo'            => $fakeLogo,
        ]);

        $response->assertRedirect(route('admin.companies.index'));

        $company = Company::where('company_code', 'HRZ')->firstOrFail();
        $this->assertNotNull($company->logo_path);

        $expectedDiskPath = public_path('storage/' . $company->logo_path);
        $this->assertFileExists($expectedDiskPath, "File must exist directly at {$expectedDiskPath}");

        // Cleanup
        if (File::exists($expectedDiskPath)) {
            File::delete($expectedDiskPath);
        }
    }

    public function test_updating_logo_replaces_old_file_in_public_storage(): void
    {
        $this->actingAs($this->superAdmin);

        $firstLogo = UploadedFile::fake()->image('logo_1.png', 100, 100);

        $this->post(route('admin.companies.store'), [
            'name'            => 'Silverline Towers',
            'company_code'    => 'SLT',
            'contact_primary' => '9123456780',
            'address'         => 'Jorhat, Assam',
            'logo'            => $firstLogo,
        ]);

        $company = Company::where('company_code', 'SLT')->firstOrFail();
        $oldPath = public_path('storage/' . $company->logo_path);
        $this->assertFileExists($oldPath);

        // Update with new logo
        $secondLogo = UploadedFile::fake()->image('logo_2.png', 150, 150);

        $response = $this->put(route('admin.companies.update', $company->id), [
            'name'            => 'Silverline Towers Renamed',
            'company_code'    => 'SLT',
            'contact_primary' => '9123456780',
            'address'         => 'Jorhat, Assam',
            'logo'            => $secondLogo,
        ]);

        $response->assertRedirect(route('admin.companies.index'));

        $company->refresh();
        $newPath = public_path('storage/' . $company->logo_path);

        $this->assertNotEquals($oldPath, $newPath);
        $this->assertFileDoesNotExist($oldPath, 'Old logo file must be deleted from public/storage.');
        $this->assertFileExists($newPath, 'New logo file must exist in public/storage.');

        // Cleanup
        if (File::exists($newPath)) {
            File::delete($newPath);
        }
    }

    public function test_deleting_company_cleans_up_logo_from_public_storage(): void
    {
        $this->actingAs($this->superAdmin);

        $logo = UploadedFile::fake()->image('logo_del.png', 100, 100);

        $this->post(route('admin.companies.store'), [
            'name'            => 'Delete Me Builders',
            'company_code'    => 'DMB',
            'contact_primary' => '9000000000',
            'address'         => 'Tezpur, Assam',
            'logo'            => $logo,
        ]);

        $company = Company::where('company_code', 'DMB')->firstOrFail();
        $logoPath = public_path('storage/' . $company->logo_path);
        $this->assertFileExists($logoPath);

        $response = $this->delete(route('admin.companies.destroy', $company->id));
        $response->assertRedirect(route('admin.companies.index'));

        $this->assertFileDoesNotExist($logoPath, 'Logo file must be deleted from public/storage upon company deletion.');
    }
}
