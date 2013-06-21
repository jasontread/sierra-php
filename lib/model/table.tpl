CREATE TABLE {$table->getName()} (
{assign var="started" value="0"}
{foreach from=$table->getColumns() item=column}
{if $started}, 
{/if}
  {$column->getName()} {$db->getColumnDefinition($table, $column, $dbRefIntegrity)}{assign var="started" value="1"}{/foreach}
{if $db->getTableDefinition($table, $dbRefIntegrity)}
, 
  {$db->getTableDefinition($table, $dbRefIntegrity)}
{/if}
){if $isMysql && ($table->getMysqlTableType() || $mysqlTableType)} ENGINE={if $table->getMysqlTableType()}{$table->getMysqlTableType()}{else}{$mysqlTableType}{/if}{/if};


{if $db->getExtraTableDdl($table)}{$db->getExtraTableDdl($table)}{/if}


{foreach from=$table->getIndexes() item=index}
CREATE{if $index->getModifier()} {$index->getModifier()}{/if} INDEX {$index->getName()} ON {$table->getName()} ({$index->getColumns()}){if $index->getPostfix()} {$index->getPostfix()}{/if};

{/foreach}
