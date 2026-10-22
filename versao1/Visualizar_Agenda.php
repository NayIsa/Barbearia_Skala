<?php
require __DIR__ . '/db.php';
$pdo = db();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'cancelar') {
    $pdo->prepare("DELETE FROM agendamentos WHERE id = ?")->execute([(int)($_POST['id'] ?? 0)]);
    $msg = 'Agendamento cancelado.';
}

$data = $_REQUEST['data'] ?? date('Y-m-d');
if (!dataValida($data)) $data = date('Y-m-d');

$st = $pdo->prepare("SELECT a.*, s.nome AS servico, s.preco
                     FROM agendamentos a JOIN servicos s ON s.id = a.servico_id
                     WHERE a.data = ? ORDER BY a.inicio");
$st->execute([$data]);
$lista = $st->fetchAll(PDO::FETCH_ASSOC);
$total = array_sum(array_column($lista, 'preco'));

topo('Agenda do dia');
?>
<h1>Agenda do dia</h1>
<div class="aviso info" role="note">Isto será visto apenas pelos funcionários em um futuro projeto</div>
<p class="sub"><?= dataBR($data) ?></p>

<?php if ($msg): ?><div class="aviso ok" role="status"><?= h($msg) ?></div><?php endif; ?>

<section>
  <form method="get">
    <label for="data">Escolher dia</label>
    <input id="data" type="date" name="data" value="<?= h($data) ?>" required>
    <button type="submit">Ver agenda</button>
  </form>
</section>

<section>
  <h2>Agendamentos</h2>
  <?php if (!$lista): ?>
    <p class="vazio">Nenhum agendamento em <?= dataBR($data) ?>.</p>
  <?php else: ?>
  <div class="tabela-wrap"><table>
    <thead><tr><th>Horário</th><th>Cliente</th><th>Telefone</th><th>Serviço</th><th>Valor</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($lista as $a):
        $t = $a['telefone'];
        $tel = strlen($t) === 11 ? sprintf('(%s) %s-%s', substr($t,0,2), substr($t,2,5), substr($t,7))
                                 : sprintf('(%s) %s-%s', substr($t,0,2), substr($t,2,4), substr($t,6)); ?>
      <tr>
        <td class="hr"><?= hora((int)$a['inicio']) ?>–<?= hora((int)$a['fim']) ?></td>
        <td><?= h($a['cliente']) ?></td>
        <td><?= h($tel) ?></td>
        <td><?= h($a['servico']) ?></td>
        <td><?= reais((float)$a['preco']) ?></td>
        <td>
          <form method="post" onsubmit="return confirm('Cancelar este agendamento?')">
            <input type="hidden" name="acao" value="cancelar">
            <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
            <input type="hidden" name="data" value="<?= h($data) ?>">
            <button class="perigo" type="submit">Cancelar</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
  <p style="margin:14px 0 0"><strong><?= count($lista) ?></strong> atendimento(s) · previsto <strong><?= reais((float)$total) ?></strong></p>
  <?php endif; ?>
</section>
<?php rodape();
