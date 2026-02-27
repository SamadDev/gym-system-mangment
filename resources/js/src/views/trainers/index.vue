<template>
  <div class="trainers-page">
    <div class="panel panel-flat">
      <div class="panel-body">
        <div class="row">
          <div class="col-md-12">
            <TrainersTable />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import TrainersTable from './TrainersTable.vue';

// Meta information
defineOptions({
  name: 'Trainers'
});
</script>

<style scoped>
.trainers-page {
  padding: 20px;
}
</style>