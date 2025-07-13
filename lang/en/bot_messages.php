<?php

return [
    'welcome_new_user' => 'Welcome, :name! I am your job application tracking assistant.',
    'main_menu' => "What would you like to do?\n\n1 - Create new application\n2 - List applications\n3 - Delete application\n4 - Update application",
    'invalid_option' => 'Invalid option. Please type the number of the desired option.',
    'application_list' => 'Here are your applications:',
    'application_create_start' => 'Let\'s start. What is the company name? (type "skip" to ignore)',
    'application_create_job_title' => 'What is the job title? (required)',
    'application_create_job_title_required' => 'The job title is required.',
    'application_create_job_description' => 'What is the job description? (type "skip" to ignore)',
    'application_create_job_salary' => 'What is the salary? (type "skip" to ignore)',
    'application_create_job_link' => 'What is the job link? (you can skip)',
    'application_create_success' => 'Application registered successfully!',
    'application_update_start' => 'What is the ID of the application you want to update?',
    'application_update' => 'Application updated successfully!',
    'application_delete_start' => 'What is the ID of the application you want to remove?',
    'application_delete' => 'Application removed successfully!',
    'application_list_header' => 'Here are your applications:',
    'application_list_item_details' => "*Application :index*\n*Company:* :company_name\n*Job Title:* :job_title\n*Description:* :job_description\n*Salary:* :job_salary\n*Link:* :job_link\n*Application Date:* :application_date",
    'application_list_prompt' => 'Enter the number of the application you want to update, or type "cancel" to return to the main menu.',
    'application_list_delete_prompt' => 'Enter the number of the application you want to delete or "cancel" to go back.',
    'application_list_empty' => 'You have not registered any applications yet.',
    'application_not_found' => 'Application not found.',

    'application_update_menu' => "You selected the job *:job_title* at *:company_name*.\n\nWhat would you like to update?\n1. Company name\n2. Job title\n3. Job description\n4. Salary\n5. Job link\n6. Cancel",
    'application_update_prompt_new_value' => 'Please enter the new value for *:field* or "skip" to keep the current value.',
    'application_updated_success' => 'Application updated successfully!',

    'application_delete_start' => 'Let\'s delete an application.',
    'application_delete_confirm' => "Are you sure you want to delete the application for the job *:job_title* at *:company_name*? Reply with 'yes' to confirm.",
    'application_delete_cancelled' => 'Deletion cancelled.',
    'application_deleted_success' => 'Application deleted successfully!',

    'application_end_conversation' => 'Thank you for using the bot. See you later!',
    'error_try_again' => 'An error occurred. Please try again.',
    'invalid_option' => 'Invalid option.',
]; 