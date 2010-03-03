<?php
/**
 * GExtenso class file
 *
 * @author Fausto Gonçalves Cintra (goncin) <goncin@gmail.com>
 * @link http://devfranca.ning.com
 * @link http://twitter.com/g0nc1n
 * @license http://creativecommons.org/licenses/LGPL/2.1/deed.pt
 */

/**
 * GExtenso é uma classe que gera a representação por extenso de um número ou valor monetário.
 *
 * ATENÇÃO: A PÁGINA DE CÓDIGO DESTE ARQUIVO É UTF-8 (Unicode)!
 * 
 * Sua implementação foi feita como prova de conceito, utilizando:
 *
 *
 * <ul>
 * <li>Métodos estáticos, implementando o padrão de projeto (<i>design pattern</i>) <b>SINGLETON</b>;</li>
 * <li>Chamadas recursivas a métodos, minimizando repetições e mantendo o código enxuto;</li>
 * <li>Uso de pseudoconstantes ('private static') diante das limitações das constantes de classe;</li>
 * <li>Tratamento de erros por intermédio de exceções; e</li>
 * <li>Utilização do phpDocumentor ({@link http://www.phpdoc.org}) para documentação do código fonte e
 * geração automática de documentação externa.</li>
 * </ul>
 *
 * <b>EXEMPLOS DE USO</b>
 *
 * Para obter o extenso de um número, utilize GExtenso::{@link numero}.
 * <pre>
 * echo GExtenso::numero(832); // oitocentos e trinta e dois
 * echo GExtenso::numero(832, GExtenso::GENERO_FEM) // oitocentas e trinta e duas
 * </pre>
 *
 * Para obter o extenso de um valor monetário, utilize GExtenso::{@link moeda}.
 * <pre>
 * // IMPORTANTE: veja nota sobre o parâmetro 'valor' na documentação do método!
 * echo GExtenso::moeda(15402); // cento e cinquenta e quatro reais e dois centavos
 * echo GExtenso::moeda(47); // quarenta e sete centavos
 * echo GExtenso::moeda(357082, 2,
 *   array('peseta', 'pesetas', GExtenso::GENERO_FEM),
 *   array('cêntimo', 'cêntimos', GExtenso::GENERO_MASC));
 *   // três mil, quinhentas e setenta pesetas e oitenta e dois cêntimos
 * </pre>
 *
 * @author Fausto Gonçalves Cintra (goncin) <goncin@gmail.com>
 * @version 0.1 2010-03-02
 * @package GUtils
 *
 */

 class GExtenso {

  const NUM_SING = 0;
  const NUM_PLURAL = 1;
  const POS_GENERO = 2;
  const GENERO_MASC = 0;
  const GENERO_FEM = 1;

  const VALOR_MAXIMO = 999999999;

  /* Uma vez que o PHP não suporta constantes de classe na forma de matriz (array),
    a saída encontrada foi declarar as strings numéricas como 'private static'.
  */
  
  /* As unidades 1 e 2 variam em gênero, pelo que precisamos de dois conjuntos de strings (masculinas e femininas) para as unidades */
  private static $UNIDADES = array(
    self::GENERO_MASC => array(
      1 => 'um',
      2 => 'dois',
      3 => 'três',
      4 => 'quatro',
      5 => 'cinco',
      6 => 'seis',
      7 => 'sete',
      8 => 'oito',
      9 => 'nove'
    ),
    self::GENERO_FEM => array(
      1 => 'uma',
      2 => 'duas',
      3 => 'três',
      4 => 'quatro',
      5 => 'cinco',
      6 => 'seis',
      7 => 'sete',
      8 => 'oito',
      9 => 'nove'
    )
  );

  private static $DE11A19 = array(
    11 => 'onze',
    12 => 'doze',
    13 => 'treze',
    14 => 'quatorze',
    15 => 'quinze',
    16 => 'dezesseis',
    17 => 'dezessete',
    18 => 'dezoito',
    19 => 'dezenove'
  );

  private static $DEZENAS = array(
    10 => 'dez',
    20 => 'vinte',
    30 => 'trinta',
    40 => 'quarenta',
    50 => 'cinquenta',
    60 => 'sessenta',
    70 => 'setenta',
    80 => 'oitenta',
    90 => 'noventa'
  );

  private static $CENTENA_EXATA = 'cem';

  /* As centenas, com exceção de 'cento', também variam em gênero. Aqui também se faz
    necessário dois conjuntos de strings (masculinas e femininas).
  */

  private static $CENTENAS = array(
    self::GENERO_MASC => array(
      100 => 'cento',
      200 => 'duzentos',
      300 => 'trezentos',
      400 => 'quatrocentos',
      500 => 'quinhentos',
      600 => 'seiscentos',
      700 => 'setecentos',
      800 => 'oitocentos',
      900 => 'novecentos'
    ),
    self::GENERO_FEM => array(
      100 => 'cento',
      200 => 'duzentas',
      300 => 'trezentas',
      400 => 'quatrocentas',
      500 => 'quinhentas',
      600 => 'seiscentas',
      700 => 'setecentas',
      800 => 'oitocentas',
      900 => 'novecentas'
    )
  );

  /* 'Mil' é invariável, seja em gênero, seja em número */
  private static $MILHAR = 'mil';

  private static $MILHOES = array(
    self::NUM_SING => 'milhão',
    self::NUM_PLURAL => 'milhões'
  );



 /**
 * Gera a representação por extenso de um número inteiro, maior que zero e menor ou igual a GExtenso::VALOR_MAXIMO.
 *
 * @param int O valor numérico cujo extenso se deseja gerar
 *
 * @param int (Opcional; valor padrão: GExtenso::GENERO_MASC) O gênero gramatical (GExtenso::GENERO_MASC ou GExtenso::GENERO_FEM)
 * do extenso a ser gerado. Isso possibilita distinguir, por exemplo, entre 'duzentos e dois homens' e 'duzentas e duas mulheres'.
 *
 * @return string O número por extenso
 *
 * @since 0.1 2010-03-02
 */
  public static function numero($valor, $genero = self::GENERO_MASC) {

    /* ----- VALIDAÇÃO DOS PARÂMETROS DE ENTRADA ---- */

    if(!is_numeric($valor))
      throw new Exception("[Exceção em GExtenso::numero] Parâmetro \$valor não é numérico (recebido: '$valor')");

    else if($valor <= 0)
      throw new Exception("[Exceção em GExtenso::numero] Parâmetro \$valor igual a ou menor que zero (recebido: '$valor')");

    else if($valor > self::VALOR_MAXIMO)
      throw new Exception('[Exceção em GExtenso::numero] Parâmetro $valor deve ser um inteiro entre 1 e ' . self::VALOR_MAXIMO . " (recebido: '$valor')");

    else if($genero != self::GENERO_MASC && $genero != self::GENERO_FEM)
      throw new Exception("Exceção em GExtenso: valor incorreto para o parâmetro \$genero (recebido: '$genero').");

    /* ----------------------------------------------- */

    else if($valor >= 1 && $valor <= 9)
      return self::$UNIDADES[$genero][$valor]; // As unidades 'um' e 'dois' variam segundo o gênero

    else if($valor == 10)
      return self::$DEZENAS[$valor];

    else if($valor >= 11 && $valor <= 19)
      return self::$DE11A19[$valor];

    else if($valor >= 20 && $valor <= 99) {
      $dezena = $valor - ($valor % 10);
      $ret = self::$DEZENAS[$dezena];
      /* Chamada recursiva à função para processar $resto se este for maior que zero.
       * O conectivo 'e' é utilizado entre dezenas e unidades.
       */
      if($resto = $valor - $dezena) $ret .= ' e ' . self::numero($resto, $genero);
      return $ret;
    }

    else if($valor == 100) {
      return self::$CENTENA_EXATA;
    }

    else if($valor >= 101 && $valor <= 999) {
      $centena = $valor - ($valor % 100);
      $ret = self::$CENTENAS[$genero][$centena]; // As centenas (exceto 'cento') variam em gênero
      /* Chamada recursiva à função para processar $resto se este for maior que zero.
       * O conectivo 'e' é utilizado entre centenas e dezenas.
       */
      if($resto = $valor - $centena) $ret .= ' e ' . self::numero($resto, $genero);
      return $ret;
    }

    else if($valor >= 1000 && $valor <= 999999) {
      /* A função 'floor' é utilizada para encontrar o inteiro da divisão de $valor por 1000,
       * assim determinando a quantidade de milhares. O resultado é enviado a uma chamada recursiva
       * da função. A palavra 'mil' não se flexiona.
       */
      $milhar = floor($valor / 1000);
      $ret = self::numero($milhar, self::GENERO_MASC) . ' ' . self::$MILHAR; // 'Mil' é do gênero masculino
      $resto = $valor % 1000;
      /* Chamada recursiva à função para processar $resto se este for maior que zero.
       * O conectivo 'e' é utilizado entre milhares e números entre 1 e 99, bem como antes de centenas exatas.
       */
      if($resto && (($resto >= 1 && $resto <= 99) || $resto % 100 == 0))
        $ret .= ' e ' . self::numero($resto, $genero);
      /* Nos demais casos, após o milhar é utilizada a vírgula. */
      else if ($resto)
        $ret .= ', ' . self::numero($resto, $genero);
      return $ret;
    }

    else if($valor >= 100000 && $valor <= self::VALOR_MAXIMO) {
      /* A função 'floor' é utilizada para encontrar o inteiro da divisão de $valor por 1000000,
       * assim determinando a quantidade de milhões. O resultado é enviado a uma chamada recursiva
       * da função. A palavra 'milhão' flexiona-se no plural.
       */
      $milhoes = floor($valor / 1000000);
      $ret = self::numero($milhoes, self::GENERO_MASC) . ' '; // Milhão e milhões são do gênero masculino
      
      /* Se a o número de milhões for maior que 1, deve-se utilizar a forma flexionada no plural */
      $ret .= $milhoes == 1 ? self::$MILHOES[self::NUM_SING] : self::$MILHOES[self::NUM_PLURAL];

      $resto = $valor % 1000000;

      /* Chamada recursiva à função para processar $resto se este for maior que zero.
       * O conectivo 'e' é utilizado entre milhões e números entre 1 e 99, bem como antes de centenas exatas.
       */
      if($resto && (($resto >= 1 && $resto <= 99) || $resto % 100 == 0))
        $ret .= ' e ' . self::numero($resto, $genero);
      /* Nos demais casos, após o milhão é utilizada a vírgula. */
      else if ($resto)
        $ret .= ', ' . self::numero($resto, $genero);
      return $ret;
    }

  }

 /**
 * Gera a representação por extenso de um valor monetário, maior que zero e menor ou igual a GExtenso::VALOR_MAXIMO.
 *
 * @param int O valor monetário cujo extenso se deseja gerar.
 * ATENÇÃO: PARA EVITAR OS CONHECIDOS PROBLEMAS DE ARREDONDAMENTO COM NÚMEROS DE PONTO FLUTUANTE, O VALOR DEVE SER PASSADO
 * JÁ DEVIDAMENTE MULTIPLICADO POR 10 ELEVADO A $casasDecimais (o que equivale, normalmente, a passar o valor com centavos
 * multiplicado por 100)
 *
 * @param int (Opcional; valor padrão: 2) Número de casas decimais a serem consideradas como parte fracionária (centavos)
 *
 * @param array (Opcional; valor padrão: array('real', 'reais', GExtenso::GENERO_MASC)) Fornece informações sobre a moeda a ser
 * utilizada. O primeiro valor da matriz corresponde ao nome da moeda no singular, o segundo ao nome da moeda no plural e o terceiro
 * ao gênero gramatical do nome da moeda (GExtenso::GENERO_MASC ou GExtenso::GENERO_FEM)
 *
 * @param array (Opcional; valor padrão: array('centavo', 'centavos', self::GENERO_MASC)) Provê informações sobre a parte fracionária
 * da moeda. O primeiro valor da matriz corresponde ao nome da parte fracionária no singular, o segundo ao nome da parte fracionária no plural
 * e o terceiro ao gênero gramatical da parte fracionária (GExtenso::GENERO_MASC ou GExtenso::GENERO_FEM)
 *
 * @return string O valor monetário por extenso
 *
 * @since 0.1 2010-03-02
 */
  public static function moeda(
    $valor,
    $casasDecimais = 2,
    $infoUnidade = array('real', 'reais', self::GENERO_MASC),
    $infoFracao = array('centavo', 'centavos', self::GENERO_MASC)
  ) {

    /* ----- VALIDAÇÃO DOS PARÂMETROS DE ENTRADA ---- */

    if(!is_numeric($valor))
      throw new Exception("[Exceção em GExtenso::moeda] Parâmetro \$valor não é numérico (recebido: '$valor')");

    else if($valor <= 0)
      throw new Exception("[Exceção em GExtenso::moeda] Parâmetro \$valor igual a ou menor que zero (recebido: '$valor')");

    else if(!is_numeric($casasDecimais) || $casasDecimais < 0)
      throw new Exception("[Exceção em GExtenso::moeda] Parâmetro \$casasDecimais não é numérico ou é menor que zero (recebido: '$casasDecimais')");

    else if(!is_array($infoUnidade) || count($infoUnidade) < 3) {
      $infoUnidade = print_r($infoUnidade, true);
      throw new Exception("[Exceção em GExtenso::moeda] Parâmetro \$infoUnidade não é uma matriz com 3 (três) elementos (recebido: '$infoUnidade')");
    }

    else if($infoUnidade[self::POS_GENERO] != self::GENERO_MASC && $infoUnidade[self::POS_GENERO] != self::GENERO_FEM)
      throw new Exception("Exceção em GExtenso: valor incorreto para o parâmetro \$infoUnidade[self::POS_GENERO] (recebido: '{$infoUnidade[self::POS_GENERO]}').");

    else if(!is_array($infoFracao) || count($infoFracao) < 3) {
      $infoFracao = print_r($infoFracao, true);
      throw new Exception("[Exceção em GExtenso::moeda] Parâmetro \$infoFracao não é uma matriz com 3 (três) elementos (recebido: '$infoFracao')");
    }

    else if($infoFracao[self::POS_GENERO] != self::GENERO_MASC && $infoFracao[self::POS_GENERO] != self::GENERO_FEM)
      throw new Exception("Exceção em GExtenso: valor incorreto para o parâmetro \$infoFracao[self::POS_GENERO] (recebido: '{$infoFracao[self::POS_GENERO]}').");

    /* ----------------------------------------------- */

    /* A parte inteira do valor monetário corresponde ao $valor passado dividido por 10 elevado a $casasDecimais, desprezado o resto.
     * Assim, com o padrão de 2 $casasDecimais, o $valor será dividido por 100 (10^2), e o resto é descartado utilizando-se floor().
     */
    $parteInteira = floor($valor / pow(10, $casasDecimais));

    /* A parte fracionária ('centavos'), por seu turno, corresponderá ao resto da divisão do $valor por 10 elevado a $casasDecimais.
     * No cenário comum em que trabalhamos com 2 $casasDecimais, será o resto da divisão do $valor por 100 (10^2).
     */
    $fracao = $valor % pow(10, $casasDecimais);

    /* O extenso para a $parteInteira somente será gerado se esta for maior que zero. Para tanto, utilizamos
     * os préstimos do método GExtenso::numero().
     */
    if($parteInteira) {
      $ret = self::numero($parteInteira, $infoUnidade[self::POS_GENERO]) . ' ';
      $ret .= $parteInteira == 1 ? $infoUnidade[self::NUM_SING] : $infoUnidade[self::NUM_PLURAL];
    }

    /* De forma semelhante, o extenso da $fracao somente será gerado se esta for maior que zero. */
    if($fracao) {
      /* Se a $parteInteira for maior que zero, o extenso para ela já terá sido gerado. Antes de juntar os
       * centavos, precisamos colocar o conectivo 'e'.
       */
      if ($parteInteira) $ret .= ' e ';
      $ret .= self::numero($fracao, $infoFracao[self::POS_GENERO]) . ' ';
      $ret .= $parteInteira == 1 ? $infoFracao[self::NUM_SING] : $infoFracao[self::NUM_PLURAL];
    }

    return $ret;

  }

}
?>