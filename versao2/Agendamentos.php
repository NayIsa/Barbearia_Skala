<?php
require __DIR__ . '/db.php';
$pdo  = db();
$hoje = date('Y-m-d');
$erro = $sucesso = '';

$entrada   = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
$servicoId = (int)($entrada['servico'] ?? 0);
$st = $pdo->prepare("SELECT * FROM servicos WHERE id = ?");
$st->execute([$servicoId]);
$servico = $st->fetch(PDO::FETCH_ASSOC);

// Sem serviço escolhido na página inicial: volta para lá.
if (!$servico) { header('Location: home.php'); exit; }

$data = $entrada['data'] ?? $hoje;
if (!dataValida($data) || $data < $hoje) $data = $hoje;
$preselecionado = isset($entrada['horario']) ? (int)$entrada['horario'] : -1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cliente  = trim($_POST['cliente'] ?? '');
    $telefone = preg_replace('/\D/', '', $_POST['telefone'] ?? '');
    $inicio   = $preselecionado;

    if ($cliente === '' || mb_strlen($cliente) > 80) $erro = 'Informe seu nome.';
    elseif (strlen($telefone) < 10 || strlen($telefone) > 11) $erro = 'Informe o telefone com DDD (10 ou 11 dígitos).';
    elseif ($inicio < 0) $erro = 'Escolha um horário.';
    else {
        $fim   = $inicio + (int)$servico['duracao'];
        $agora = ($data === $hoje) ? (int)date('G') * 60 + (int)date('i') : -1;
        if ($inicio < ABERTURA || $fim > FECHAMENTO || $inicio <= $agora) {
            $erro = 'Horário fora do atendimento (' . hora(ABERTURA) . ' às ' . hora(FECHAMENTO) . ') ou já passou.';
        } else {
            $pdo->exec('BEGIN IMMEDIATE');
            try {
                if (haConflito($pdo, $data, $inicio, $fim)) {
                    $pdo->exec('ROLLBACK');
                    $erro = 'Esse horário acabou de ser ocupado. Escolha outro.';
                } else {
                    $pdo->prepare("INSERT INTO agendamentos (servico_id, cliente, telefone, data, inicio, fim) VALUES (?,?,?,?,?,?)")
                        ->execute([$servicoId, $cliente, $telefone, $data, $inicio, $fim]);
                    $pdo->exec('COMMIT');
                    $sucesso = "Agendamento confirmado: {$servico['nome']} em " . dataBR($data) . ' às ' . hora($inicio) . ' (termina às ' . hora($fim) . ').';
                }
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) $pdo->exec('ROLLBACK');
                $erro = 'Não foi possível salvar o agendamento. Tente novamente.';
            }
        }
    }
}

$livres = $sucesso ? [] : horariosLivres($pdo, $data, (int)$servico['duracao']);
if (!in_array($preselecionado, $livres, true)) $preselecionado = -1;

topo('Agendar ' . $servico['nome']);
?>
<h1>Agendar <?= h($servico['nome']) ?></h1>
<p class="sub">Atendimento das <?= hora(ABERTURA) ?> às <?= hora(FECHAMENTO) ?>.</p>

<?php if ($sucesso): ?><div class="aviso ok" role="status"><?= h($sucesso) ?></div><?php endif; ?>
<?php if ($erro):    ?><div class="aviso erro" role="alert"><?= h($erro) ?></div><?php endif; ?>

<section class="resumo">
  <h2>Seu serviço</h2>
  <dl>
    <dt>Serviço</dt><dd><?= h($servico['nome']) ?></dd>
    <dt>Duração</dt><dd><?= (int)$servico['duracao'] ?> min</dd>
    <dt>Preço</dt><dd><?= reais((float)$servico['preco']) ?></dd>
    <dt>Data</dt><dd><?= dataBR($data) ?></dd>
  </dl>
</section>

<?php if ($sucesso): ?>
  <a class="btn" href="home.php">Voltar ao início</a>
<?php else: ?>

<section>
  <h2>Data</h2>
  <form method="get">
    <input type="hidden" name="servico" value="<?= $servicoId ?>">
    <label for="data">Trocar a data</label>
    <input id="data" type="date" name="data" value="<?= h($data) ?>" min="<?= $hoje ?>" required>
    <button type="submit" class="btn sec">Ver horários desta data</button>
  </form>
</section>

<section>
  <h2>Horário e seus dados</h2>
  <?php if (!$livres): ?>
    <p class="vazio">Não há horários livres em <?= dataBR($data) ?>. Escolha outra data acima.</p>
  <?php else: ?>
  <form method="post">
    <input type="hidden" name="servico" value="<?= $servicoId ?>">
    <input type="hidden" name="data" value="<?= h($data) ?>">
    <fieldset style="border:0;padding:0;margin:0">
      <legend style="font-weight:600">Horários livres em <?= dataBR($data) ?></legend>
      <div class="horarios">
        <?php foreach ($livres as $i => $m): ?>
          <label style="margin:0;font-weight:400">
            <input type="radio" name="horario" value="<?= $m ?>" <?= $m === $preselecionado ? 'checked' : '' ?> <?= $i === 0 ? 'required' : '' ?>>
            <span><?= hora($m) ?></span>
          </label>
        <?php endforeach; ?>
      </div>
    </fieldset>
    <label for="cliente">Nome</label>
    <input id="cliente" name="cliente" maxlength="80" required autocomplete="name" value="<?= h($_POST['cliente'] ?? '') ?>">
    <label for="telefone">Telefone com DDD</label>
    <input id="telefone" name="telefone" inputmode="tel" required autocomplete="tel" placeholder="(51) 99999-9999" value="<?= h($_POST['telefone'] ?? '') ?>">
    <button type="submit">Confirmar agendamento</button>
    <a class="btn sec" href="home.php">Voltar</a>
  </form>
  <?php endif; ?>
</section>

<?php endif; ?>
<?php rodape();
