<?php

namespace App\Livewire\Admin;

use App\Models\Company;
use Livewire\Component;

class Settings extends Component
{
    public bool $print_ticket_on_entry = false;

    public bool $print_ticket_on_exit = false;

    public string $primary_color = '#1e40af';

    public string $accent_color = '#f59e0b';

    public function mount(): void
    {
        $company = auth()->user()->company;

        if ($company) {
            $this->print_ticket_on_entry = $company->print_ticket_on_entry;
            $this->print_ticket_on_exit = $company->print_ticket_on_exit;
            $this->primary_color = $company->primary_color;
            $this->accent_color = $company->accent_color;
        }
    }

    public function save(): void
    {
        $this->validate([
            'primary_color' => ['required', 'string'],
            'accent_color' => ['required', 'string'],
        ]);

        $company = auth()->user()->company;

        if (! $company) {
            return;
        }

        $company->update([
            'print_ticket_on_entry' => (bool) $this->print_ticket_on_entry,
            'print_ticket_on_exit' => (bool) $this->print_ticket_on_exit,
            'primary_color' => $this->primary_color,
            'accent_color' => $this->accent_color,
        ]);

        session()->flash('success', 'Configurações salvas.');
    }

    public function render()
    {
        return view('livewire.admin.settings')->layout('components.layouts.admin', ['title' => 'Configurações', 'subtitle' => 'Personalize seu estacionamento']);
    }
}
