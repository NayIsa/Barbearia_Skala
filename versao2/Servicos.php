<?php
require __DIR__ . '/db.php';
$pdo = db();
$msg = $erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    if ($acao === 'cadastrar') {
        $nome    = trim($_POST['nome'] ?? '');
        $duracao = (int)($_POST['duracao'] ?? 0);
        $preco   = (float)str_replace(',', '.', str_replace('.', '', $_POST['preco'] ?? ''));
        if ($nome === '' || mb_strlen($nome) > 60) {
            $erro = 'Informe o nome do serviço (até 60 caracteres).';
        } elseif ($duracao < 5 || $duracao > (FECHAMENTO - ABERTURA)) {
            $erro = 'A duração deve ser de pelo menos 5 minutos e caber no horário de atendimento.';
        } elseif ($preco <= 0) {
            $erro = 'Informe um preço maior que zero.';
        } else {
            $pdo->prepare("INSERT INTO servicos (nome, duracao, preco) VALUES (?, ?, ?)")
                ->execute([$nome, $duracao, $preco]);
            $msg = 'Serviço cadastrado.';
        }
    } elseif ($acao === 'excluir') {
        $id = (int)($_POST['id'] ?? 0);
        $st = $pdo->prepare("SELECT COUNT(*) FROM agendamentos WHERE servico_id = ?");
        $st->execute([$id]);
        if ((int)$st->fetchColumn() > 0) {
            $erro = 'Este serviço tem agendamentos e não pode ser excluído.';
        } else {
            $pdo->prepare("DELETE FROM servicos WHERE id = ?")->execute([$id]);
            $msg = 'Serviço excluído.';
        }
    }
}

$servicos = $pdo->query("SELECT * FROM servicos ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
topo('Cadastro de serviços');
?>
<h1>Cadastro de serviços</h1>
<div class="aviso info" role="note">Isto será visto apenas pelos funcionários em um futuro projeto</div>

<?php if ($msg):  ?><div class="aviso ok" role="status"><?= h($msg) ?></div><?php endif; ?>
<?php if ($erro): ?><div class="aviso erro" role="alert"><?= h($erro) ?></div><?php endif; ?>

<section>
  <h2>Cadastrar serviço</h2>
  <form method="post">
    <input type="hidden" name="acao" value="cadastrar">
    <label for="nome">Nome</label>
    <input id="nome" name="nome" maxlength="60" required placeholder="Ex.: Corte">
    <div class="linha">
      <div>
        <label for="duracao">Duração (minutos)</label>
        <input id="duracao" name="duracao" type="number" min="5" max="540" step="5" required placeholder="40">
      </div>
      <div>
        <label for="preco">Preço (R$)</label>
        <input id="preco" name="preco" inputmode="decimal" required placeholder="50,00">
      </div>
    </div>
    <button type="submit">Cadastrar serviço</button>
  </form>
</section>

<section>
  <h2>Serviços cadastrados</h2>
  <?php if (!$servicos): ?>
    <p class="vazio">Nenhum serviço cadastrado. Use o formulário acima para criar o primeiro.</p>
  <?php else: ?>
  <div class="tabela-wrap"><table>
    <thead><tr><th>Serviço</th><th>Duração</th><th>Preço</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($servicos as $s): ?>
      <tr>
        <td><?= h($s['nome']) ?></td>
        <td><?= (int)$s['duracao'] ?> min</td>
        <td><?= reais((float)$s['preco']) ?></td>
        <td>
          <form method="post" onsubmit="return confirm('Excluir este serviço?')">
            <input type="hidden" name="acao" value="excluir">
            <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
            <button class="perigo" type="submit">Excluir</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
  <?php endif; ?>
</section>
<?php rodape();
