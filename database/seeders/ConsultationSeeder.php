<?php

namespace Database\Seeders;

use App\Models\Consultation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ConsultationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $consultations = [
            [
                'full_name' => 'John Smith',
                'email' => 'john.smith@example.com',
                'phone_number' => '+1-555-0123',
                'preferred_date' => Carbon::now()->addDays(7),
                'service_type' => 'Custom Curtain Design',
                'additional_message' => 'I need help designing custom curtains for my living room. Looking for something elegant and modern.',
                'status' => 'pending'
            ],
            [
                'full_name' => 'Sarah Johnson',
                'email' => 'sarah.johnson@example.com',
                'phone_number' => '+1-555-0456',
                'preferred_date' => Carbon::now()->addDays(10),
                'service_type' => 'Window Treatment Consultation',
                'additional_message' => 'Interested in blackout curtains for bedroom and sheer curtains for living room.',
                'status' => 'confirmed'
            ],
            [
                'full_name' => 'Michael Brown',
                'email' => 'michael.brown@example.com',
                'phone_number' => '+1-555-0789',
                'preferred_date' => Carbon::now()->addDays(14),
                'service_type' => 'Commercial Installation',
                'additional_message' => 'Need consultation for office building window treatments. Multiple rooms involved.',
                'status' => 'pending'
            ],
            [
                'full_name' => 'Emily Davis',
                'email' => 'emily.davis@example.com',
                'phone_number' => '+1-555-0321',
                'preferred_date' => Carbon::now()->addDays(5),
                'service_type' => 'Repair and Maintenance',
                'additional_message' => 'Existing curtains need repair. Some hardware is broken.',
                'status' => 'completed'
            ],
            [
                'full_name' => 'David Wilson',
                'email' => 'david.wilson@example.com',
                'phone_number' => '+1-555-0654',
                'preferred_date' => Carbon::now()->addDays(21),
                'service_type' => 'Custom Curtain Design',
                'additional_message' => 'Looking for eco-friendly materials and sustainable options.',
                'status' => 'pending'
            ]
        ];

        foreach ($consultations as $consultation) {
            Consultation::create($consultation);
        }
    }
}
