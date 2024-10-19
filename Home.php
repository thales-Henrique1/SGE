<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login/login.php");
    exit();
}

$nomeUsuario = $_SESSION['usuario_nome'];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Estilos/home/style.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <title>Início</title>
</head>

<body>
    <menu class="Meu_Item">
        <a href="home.php" class="Item_Menu" style="text-decoration: underline;">Início</a>
        <a href="atividadehome.php" class="Item_Menu">Atividade</a>   
        <a href="materiahome.php" class="Item_Menu">Matérias</a>  
    </menu>
    
    <div class="Display-flex">
        <div class="card-historia" style="margin-left: 15px;">
            <h1>Bem-vindo de volta, <?php echo htmlspecialchars($nomeUsuario); ?>!</h1>
            <p>Plataforma responsável pelo controle e gerenciamento dos conteúdos e matérias relacionados a concursos públicos. Tem como objetivo principal auxiliar o concursando a ter uma análise mais precisa do tempo estudado e assim otimizar sua preparação para o concurso desejado.</p>
        </div>
        <div class="gráfico"></div>
        <div class="redirecionar-atividade">
            <p>𝐋𝐢𝐬𝐭𝐚 𝐝𝐞 𝐀𝐭𝐢𝐯𝐢𝐝𝐚𝐝𝐞𝐬</p>
            <div class="red-atv">
                <a href="atividadehome.php">Ir para as atividades</a>
            </div>
        </div>
    </div>
    <center>
        <div class="container">
            <form id="atividadeForm">
                <select id="materias" name="materia">
                    <option value="0">Matérias</option>
                </select>
                <select id="conteudo" name="conteudo" disabled>
                    <option value="0">Conteúdo</option>
                </select>
                <input type="button" value="Iniciar" class="IN" id="iniciarCronometro" disabled>
                <div class="button-group">
                    <input type="button" value="Salvar" class="SAL" style="display: none;">
                    <input type="button" value="Cancelar" class="CAN" style="display: none;">
                </div>

                <div id="cronometro" style="display: none;">Tempo: 00:00</div>
            </form>
        </div>
    </center>

    <script>
        $(document).ready(function() {
            $.ajax({
                url: 'carregar_materias.php', 
                type: 'POST',
                success: function(response) {
                    $('#materias').append(response);
                }
            });

            $('#materias').on('change', function() {
                var materiaId = $(this).val();
                $('#conteudo').html('<option value="0">Conteúdo</option>').prop('disabled', true); 
                $('#iniciarCronometro').prop('disabled', true);
                
                if (materiaId !== '0') {
                    $.ajax({
                        url: 'carregar_conteudos.php', 
                        type: 'POST',
                        data: { materia_id: materiaId },
                        success: function(response) {
                            $('#conteudo').html(response).prop('disabled', false);
                            $('#iniciarCronometro').prop('disabled', false);
                        }
                    });
                }
            });

            var cronometro;
            var segundos = 0;

            function atualizarCronometro() {
                var horas = Math.floor(segundos / 3600);
                var minutos = Math.floor((segundos % 3600) / 60);
                var segundosRestantes = segundos % 60;
                var tempoFormatado = `${String(horas).padStart(2, '0')}:${String(minutos).padStart(2, '0')}:${String(segundosRestantes).padStart(2, '0')}`;
                $('#cronometro').text('Tempo: ' + tempoFormatado);
            }

            $('#iniciarCronometro').on('click', function() {
                cronometro = setInterval(function() {
                    segundos++;
                    atualizarCronometro();
                }, 1000); 

                $('.IN').hide();
                $('.SAL').show();
                $('.CAN').show();
                $('#cronometro').show();
            });

            $('.SAL').on('click', function() {
                if ($('#materias').val() === '0' || $('#conteudo').val() === '0') {
                    alert('Por favor, selecione uma matéria e um conteúdo antes de salvar.');
                    return;
                }

                clearInterval(cronometro);
                var idMateria = $('#materias').val();
                var idConteudo = $('#conteudo').val();

                $.ajax({
                    url: 'salvar_atividade.php',
                    type: 'POST',
                    data: {
                        idMateria: idMateria,
                        idConteudo: idConteudo,
                        nrHora: Math.floor(segundos / 3600),
                        nrMinuto: Math.floor((segundos % 3600) / 60),
                        nrSegundo: segundos % 60
                    },
                    success: function(response) {
                        console.log('Atividade salva com sucesso: ', response); 
                    },
                    error: function(error) {
                        console.error('Erro ao salvar atividade:', error);
                    }
                });

                $('.IN').show();
                $('.SAL').hide();
                $('.CAN').hide();
                $('#cronometro').hide();
                segundos = 0; 
                atualizarCronometro();
            });

            $('.CAN').on('click', function() {
                clearInterval(cronometro);
                segundos = 0;
                atualizarCronometro();
                $('.IN').show();
                $('.SAL').hide();
                $('.CAN').hide();
                $('#cronometro').hide();
            });
        });
    </script>
</body>
</html>


    