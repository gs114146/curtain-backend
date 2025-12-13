<?php

namespace Database\Seeders;

use App\Models\ContactUs;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContactUsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $contactMessages = [
            [
                'full_name' => 'Alice Johnson',
                'email' => 'alice.johnson@example.com',
                'phone_number' => '+1-555-0101',
                'project_type' => 'Residential',
                'subject' => 'Custom Curtain Design Inquiry',
                'message' => 'I am interested in getting custom curtains designed for my new home. I have large windows in the living room and need something elegant and modern. Could you please provide more information about your design services and pricing?',
                'status' => 'unread'
            ],
            [
                'full_name' => 'Robert Smith',
                'email' => 'robert.smith@example.com',
                'phone_number' => '+1-555-0202',
                'project_type' => 'Commercial',
                'subject' => 'Office Building Window Treatments',
                'message' => 'We are renovating our office building and need professional window treatments for all floors. We are looking for something that provides privacy while maintaining a professional appearance. Please contact us to discuss our requirements.',
                'status' => 'read'
            ],
            [
                'full_name' => 'Maria Garcia',
                'email' => 'maria.garcia@example.com',
                'phone_number' => '+1-555-0303',
                'project_type' => 'Residential',
                'subject' => 'Blackout Curtains for Bedroom',
                'message' => 'I need blackout curtains for my bedroom as I work night shifts. The curtains need to completely block out light and be easy to maintain. Do you have any recommendations for materials and installation?',
                'status' => 'replied'
            ],
            [
                'full_name' => 'David Chen',
                'email' => 'david.chen@example.com',
                'phone_number' => '+1-555-0404',
                'project_type' => 'Hospitality',
                'subject' => 'Hotel Curtain Installation',
                'message' => 'We are opening a new boutique hotel and need high-quality curtains for all guest rooms and common areas. We are looking for a long-term partnership with a reliable supplier. Please send us your portfolio and pricing information.',
                'status' => 'unread'
            ],
            [
                'full_name' => 'Sarah Williams',
                'email' => 'sarah.williams@example.com',
                'phone_number' => '+1-555-0505',
                'project_type' => 'Residential',
                'subject' => 'Curtain Repair Service',
                'message' => 'I have some existing curtains that need repair. The hardware is broken and some of the fabric is torn. Can you provide repair services or would it be better to replace them entirely? Please let me know your recommendations.',
                'status' => 'closed'
            ],
            [
                'full_name' => 'Michael Brown',
                'email' => 'michael.brown@example.com',
                'phone_number' => '+1-555-0606',
                'project_type' => 'Educational',
                'subject' => 'School Auditorium Curtains',
                'message' => 'Our school auditorium needs new stage curtains for performances. We need something durable that can handle frequent use and is fire-resistant. We have a limited budget, so please provide cost-effective options.',
                'status' => 'read'
            ]
        ];

        foreach ($contactMessages as $message) {
            ContactUs::create($message);
        }
    }
}
