<?php

return [
    'max_annual_individual_donation_idr' => (int) env('MAX_ANNUAL_INDIVIDUAL_DONATION_IDR', 1000000000),
    'max_annual_corporate_donation_idr' => (int) env('MAX_ANNUAL_CORPORATE_DONATION_IDR', 7500000000),
    'max_annual_member_dues_idr' => (int) env('MAX_ANNUAL_MEMBER_DUES_IDR', 120000000),
    'minimum_political_education_ratio' => (float) env('MINIMUM_POLITICAL_EDUCATION_RATIO', 0.30),
];
