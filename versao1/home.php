<?php
require __DIR__ . '/db.php';
$pdo  = db();
$hoje = date('Y-m-d');
$data = $_GET['data'] ?? $hoje;
if (!dataValida($data) || $data < $hoje) $data = $hoje;

$servicos = $pdo->query("SELECT * FROM servicos ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
const MAX_CHIPS = 12;

topo('Início');
?>
<h1>Escolha seu serviço</h1>
<p class="sub">Atendimento das <?= hora(ABERTURA) ?> às <?= hora(FECHAMENTO) ?>. Veja os horários livres e clique em Agendar.</p>

<section>
  <form method="get">
    <label for="data">Ver horários do dia</label>
    <input id="data" type="date" name="data" value="<?= h($data) ?>" min="<?= $hoje ?>" required>
    <button type="submit">Atualizar horários</button>
  </form>
</section>

<?php if (!$servicos): ?>
  <section><p class="vazio">Nenhum serviço disponível no momento.</p></section>
<?php else: ?>
<div class="cards">
<?php foreach ($servicos as $s):
    $livres = horariosLivres($pdo, $data, (int)$s['duracao']);
    $base   = 'Agendamentos.php?servico=' . (int)$s['id'] . '&data=' . urlencode($data); ?>
  <article class="card">
    <div class="topo">
      <h2><?= h($s['nome']) ?></h2>
      <span class="preco"><?= reais((float)$s['preco']) ?></span>
    </div>
    <p class="meta">Duração: <?= (int)$s['duracao'] ?> min</p>

    <?php if (!$livres): ?>
      <p class="vazio">Sem horários livres em <?= dataBR($data) ?>. Escolha outro dia acima.</p>
    <?php else: ?>
      <strong style="font-size:14px">Horários livres em <?= dataBR($data) ?></strong>
      <div class="chips">
        <?php foreach (array_slice($livres, 0, MAX_CHIPS) as $m): ?>
          <a href="<?= $base ?>&horario=<?= $m ?>"><?= hora($m) ?></a>
        <?php endforeach; ?>
        <?php if (count($livres) > MAX_CHIPS): ?>
          <a href="<?= $base ?>">+<?= count($livres) - MAX_CHIPS ?> horários</a>
        <?php endif; ?>
      </div>
      <a class="btn" href="<?= $base ?>">Agendar <?= h($s['nome']) ?></a>
    <?php endif; ?>
  </article>
<?php endforeach; ?>
</div>
<?php endif; ?>
<?php rodape();
