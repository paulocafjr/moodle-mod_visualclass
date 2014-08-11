<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


/**
 * This file defines mod_visualclass strings for pt_br
 *
 * @package    mod
 * @subpackage visualclass
 * @copyright  2013 Caltech Informática Ltda <class@class.com.br>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// A-Z Sort

// E
$string['error_maxattemptsreached'] = 'Número máximo de tentativas atingido';
$string['error_nocapability'] = 'Você não possui a capacidade de visualizar o módulo';
$string['error_nohome'] = 'O diretório "visualclass" não se encontra na raiz '
    . 'do Moodle. Por favor crie o diretório e de direitos de gravação '
    . 'para seu servidor web';
$string['error_noitems'] = 'A sessão não possui itens';
$string['error_nosessions'] = 'A atividade não possui nenhuma sessão';
$string['error_unknown'] = 'Erro desconhecido';
// F
$string['felem_attempts'] = 'Política de Tentativas';
$string['felem_attempts_unlimited'] = 'sem limite de tentativas';
$string['felem_file'] = 'Arquivos do Projeto';
$string['felem_file_help'] = 'Suba o arquivo ZIP produzido pelo GeraHTML '
    . 'para a aula Visual Class desejada';
$string['felem_grades'] = 'Política de Notas';
$string['felem_grades_average'] = 'média';
$string['felem_grades_best'] = 'melhor resultado';
$string['felem_grades_worst'] = 'pior resultado';
$string['felem_header_settings'] = 'Configurações';
$string['felem_name'] = 'Nome';
$string['felem_name_help'] = 'Identifica a atividade no curso';
$string['felem_projectsubject'] = 'Assunto do Projeto';
$string['felem_time'] = 'Política de Tempo';
$string['felem_time_unit'] = 'minutos';
$string['felem_time_unlimited'] = 'sem limite de tempo';
$string['felem_view'] = 'Política de Visualização';
$string['felem_view_moodle'] = 'dentro do moodle';
$string['felem_view_newtab'] = 'nova aba';
$string['felem_view_popup'] = 'popup';
$string['felem_view_popup_width'] = 'largura';
$string['felem_view_popup_height'] = 'altura';
// M
$string['modulename'] = 'Visual Class';
$string['modulenameplural'] = 'Visual Class';
$string['modulename_help'] = '<h4 style="text-align:center;">Visual Class</h4>'
    . '<p style="text-align:justify;">O Visual Class é um software de autoria '
    . 'brasileiro, desenvolvido pela Caltech Informática Ltda, para a criação '
    . 'de aulas com recursos multimídia.</p>'
    . '<p style="text-align:justify;">Este módulo de atividades permite o uso '
    . 'destas aulas no Moodle. Para tanto, é necessário o uso da ferramenta '
    . 'GeraHTML, que converte as aulas Visual Class em scripts para a Web.</p>'
    . '<p style="text-align:justify;">Para saber mais sobre o Visual Class e '
    . 'outras soluções acesse <a href="http://www.class.com.br">class.com.br.'
    . '</a></p>'
    . '<p style="font-size:x-small; text-align:center">Visual Class&reg; e '
    . 'GeraHTML&reg; são marcas registradas pela Caltech Informática Ltda</p>';
// N
$string['noanswer'] = 'não respondido';
// P
$string['pluginadministration'] = 'Administração Visual Class';
$string['pluginname'] = 'Módulo de Atividades Visual Class';
$string['projecthome'] = 'Pasta para projetos';
// R
$string['report_answercorrect'] = 'Resposta Correta';
$string['report_answeruser'] = 'Resposta do Aluno';
$string['report_attempt'] = 'Número da Tentativa';
$string['report_headerdetailed'] = 'Relatório Detalhado de Desempenho por Aluno';
$string['report_headerquestion'] = 'Relatório Detalhado de Desempenho por Questão';
$string['report_iscorrect'] = 'correto';
$string['report_iswrong'] = 'errado';
$string['report_pagetitle'] = 'Código da Tela';
$string['report_percent'] = 'Porcentagem de Acertos';
$string['report_percentcorrect'] = 'Respostas Corretas';
$string['report_percenttotal'] = 'Total';
$string['report_percentwrong'] = 'Respostas Erradas';
$string['report_question'] = 'Questão';
$string['report_separator'] = ' ou ';
$string['report_time'] = 'Tempo';
$string['report_time_zero'] = 'Menos de um minuto';
$string['report_totalscore'] = 'Nota';
$string['report_type'] = 'Tipo';
$string['report_typepreenchimento'] = 'Preenchimento';
$string['report_typetestevestibular'] = 'Teste Vestibular';
$string['report_username'] = 'Aluno';
// S
$string['status_buttonok'] = 'Sair';
$string['status_labelcorrect'] = 'Respostas Corretas';
$string['status_labelscore'] = 'Nota';
$string['status_labelwrong'] = 'Respostas Erradas';
$string['status_sessionadmin'] = 'Esta sessão não será salva';
$string['status_sessionok'] = 'Sessão concluída com sucesso';
$string['status_timeout'] = 'Tempo esgotado';
// T
$string['text_adminprivileges1'] = 'Você pode visualizar o relatório detalhado por aluno da atividade. '
    . 'Deseja continuar?';
$string['text_adminprivileges2'] = 'Você pode visualizar o relatório detalhado por questão da atividade. '
    . 'Deseja continuar?';
$string['text_gotoproject'] = 'Você será redirecionado para a página do projeto. Deseja continuar?';
$string['text_popup'] = 'Uma janela popup será aberta. Por favor verifique se seu navegador não está '
    . 'bloqueando este tipo de janela. Deseja continuar?';
$string['text_popup_view'] = 'Abrir popup';
// V
$string['visualclass'] = 'Visual Class';
$string['visualclass:addinstance'] = 'Tem a capacidade de adicionar novos módulos';
$string['visualclass:reports'] = 'Tem a capacidade de visualizar os relatórios customizados do módulo';
$string['visualclass:submit'] = 'Tem a capacidade de submeter um módulo';
$string['visualclass:view'] = 'Tem a capacidade de visualizar um módulo';