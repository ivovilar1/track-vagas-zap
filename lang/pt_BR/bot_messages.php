<?php

return [
    'welcome_new_user' => "Olá :name! Seja bem-vindo ao TrackVagaZap, seu assistente pessoal para gerenciar candidaturas de emprego.",
    'main_menu' => "Certo! O que você gostaria de fazer?\n\n" .
                   "➡️ *1. Listar Candidaturas*\n" .
                   "➡️ *2. Cadastrar Candidatura*\n" .
                   "➡️ *3. Remover Candidatura*\n" .
                   "➡️ *4. Sair*\n\n" .
                   "Por favor, digite o *número* da opção desejada(ex: 1).",
    'application_created_success' => "Perfeito, :name! Sua candidatura foi cadastrada com sucesso.",
    'application_list_success' => "Aqui estão todas as suas candidaturas:\n\n",
    'application_removed_success' => "Candidatura removida com sucesso.",
    'application_updated_success' => "Candidatura atualizada com sucesso.",
    'application_not_found' => "Candidatura não encontrada.",
    'application_list_empty' => "Você ainda não cadastrou nenhuma candidatura.",
    'application_list_item_details' => "➡️ *:index. :job_title em :company_name*\n" .
        "    - *Empresa:* :company_name\n" .
        "    - *Vaga:* :job_title\n" .
        "    - *Descrição:* :job_description\n" .
        "    - *Salário:* :job_salary\n" .
        "    - *Link:* :job_link\n" .
        "    - *Data:* :application_date",
    'application_list_item_empty' => "Nenhuma candidatura encontrada.",
    'application_list_item_not_found' => "Candidatura não encontrada.",
    'application_list' => "Aqui estão suas candidaturas:",
    'application_list_header' => "Aqui estão suas candidaturas:",
    'application_list_prompt' => "Digite o número da candidatura que deseja editar ou 'cancelar' para voltar ao menu principal.",
    'application_list_delete_prompt' => 'Digite o número da candidatura que deseja excluir ou "cancelar" para voltar.',
    'application_create_start' => 'Vamos começar. Qual o nome da empresa? (digite "pular" para ignorar)',
    'application_create_job_title' => 'Qual o título da vaga? (obrigatório)',
    'application_create_job_title_required' => 'O título da vaga é obrigatório.',
    'application_create_job_description' => 'Qual a descrição da vaga? (digite "pular" para ignorar)',
    'application_create_job_salary' => 'Qual o salário? (digite "pular" para ignorar)',
    'application_create_job_link' => 'Qual o link da vaga? (pode pular)',
    'application_create_success' => 'Candidatura cadastrada com sucesso!',
    'application_update_start' => 'Qual o ID da candidatura que você deseja atualizar?',
    'application_update' => 'Candidatura atualizada com sucesso!',
    'application_delete_start' => 'Vamos excluir uma candidatura.',
    'application_delete_confirm' => "Você tem certeza que deseja excluir a candidatura para a vaga *:job_title* na empresa *:company_name*? Responda com 'sim' para confirmar.",
    'application_delete_cancelled' => 'Exclusão cancelada.',
    'application_deleted_success' => 'Candidatura excluída com sucesso!',
    'invalid_option' => 'Opção inválida.',
    'error_try_again' => 'Ocorreu um erro. Por favor, tente novamente.',
    'application_update_menu' => "Você selecionou a vaga *:job_title* na empresa *:company_name*.\n\nO que você gostaria de atualizar?\n\n" .
        "➡️ *1. Nome da Empresa*\n" .
        "➡️ *2. Título da Vaga*\n" .
        "➡️ *3. Descrição da Vaga*\n" .
        "➡️ *4. Salário*\n" .
        "➡️ *5. Link da Vaga*\n" .
        "➡️ *6. Cancelar*\n\n" .
        "Por favor, digite o *número* da opção desejada.",
    'application_update_prompt_new_value' => 'Por favor, digite o novo valor para *:field* ou "pular" para manter o valor atual.',
    'application_fields' => [
        'company_name' => 'Nome da Empresa',
        'job_title' => 'Título da Vaga',
        'job_description' => 'Descrição da Vaga',
        'job_salary' => 'Salário',
        'job_link' => 'Link da Vaga',
    ],
    'application_handle_cancel' => 'Operação cancelada. Voltando ao menu...',
    'application_end_conversation' => 'Obrigado por utilizar o TrackVagaZap. Até mais!',
];