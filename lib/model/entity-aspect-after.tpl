{foreach from=$entity->getAopAspects($aopClassType, $aopMethodName, $smarty.const.SRA_AOP_ASPECT_WHEN_AFTER) item=aspect}
    // aop aspect
    {if $aspect->getComment()}{$aspect->getComment()}{/if}
    {$aspect->getAdvice()}
{/foreach}
