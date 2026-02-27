<template>
  <div class="equipment-page">
    <div class="panel panel-flat">
      <div class="panel-body">
        <div class="row">
          <div class="col-md-12">
            <EquipmentTable />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import EquipmentTable from './EquipmentTable.vue';

// Meta information
defineOptions({
  name: 'Equipment'
});
</script>

<style scoped>
.equipment-page {
  padding: 20px;
}
</style>