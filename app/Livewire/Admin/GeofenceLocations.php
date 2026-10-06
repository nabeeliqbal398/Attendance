<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\GeofenceLocation;
use Laravel\Jetstream\InteractsWithBanner;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class GeofenceLocations extends Component
{
    use InteractsWithBanner;

    public ?int $editingId = null;
    public string $name = '';
    public ?string $address = null;
    public string $latitude = '';
    public string $longitude = '';
    public $radius = 100;
    public bool $is_active = true;

    public bool $confirmingDeletion = false;
    public ?int $deleteId = null;
    public string $deleteName = '';

    public function mount(): void
    {
        if (! auth()->check() || ! auth()->user()->isAdmin) {
            abort(403);
        }
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius' => ['required', 'integer', 'min:1', 'max:10000'],
            'is_active' => ['boolean'],
        ]);

        $isEditing = (bool) $this->editingId;
        $location = $isEditing
            ? GeofenceLocation::findOrFail($this->editingId)
            : new GeofenceLocation();

        $location->fill([
            'name' => $data['name'],
            'address' => $data['address'],
            'latitude' => (float) $data['latitude'],
            'longitude' => (float) $data['longitude'],
            'radius' => (int) $data['radius'],
            'is_active' => (bool) $data['is_active'],
        ])->save();

        ActivityLog::record(
            $isEditing ? 'Update Geofence Location' : 'Create Geofence Location',
            ($isEditing ? 'Updated' : 'Created') . ' geofence location: ' . $location->name
        );

        $this->resetForm();
        $this->banner(__('Geofence location saved.'));
    }

    public function edit(int $id): void
    {
        $location = GeofenceLocation::findOrFail($id);

        $this->editingId = $location->id;
        $this->name = $location->name;
        $this->address = $location->address;
        $this->latitude = (string) $location->latitude;
        $this->longitude = (string) $location->longitude;
        $this->radius = $location->radius;
        $this->is_active = $location->is_active;
    }

    public function toggleActive(int $id): void
    {
        $location = GeofenceLocation::findOrFail($id);
        $location->update(['is_active' => ! $location->is_active]);

        ActivityLog::record('Toggle Geofence Location', 'Toggled geofence location: ' . $location->name);
    }

    public function confirmDeletion(int $id): void
    {
        $location = GeofenceLocation::findOrFail($id);

        $this->deleteId = $location->id;
        $this->deleteName = $location->name;
        $this->confirmingDeletion = true;
    }

    public function delete(): void
    {
        if (! $this->deleteId) {
            return;
        }

        $location = GeofenceLocation::findOrFail($this->deleteId);
        $name = $location->name;
        $location->delete();

        ActivityLog::record('Delete Geofence Location', 'Deleted geofence location: ' . $name);

        $this->confirmingDeletion = false;
        $this->deleteId = null;
        $this->deleteName = '';
        $this->banner(__('Geofence location deleted.'));
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->address = null;
        $this->latitude = '';
        $this->longitude = '';
        $this->radius = 100;
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.geofence-locations', [
            'locations' => GeofenceLocation::query()
                ->orderByDesc('is_active')
                ->orderBy('name')
                ->get(),
        ]);
    }
}
