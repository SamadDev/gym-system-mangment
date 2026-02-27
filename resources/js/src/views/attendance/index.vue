<template>
  <div class="attendance-page">
    <div class="panel panel-flat">
      <div class="panel-body">
        <div class="row">
          <div class="col-md-12">
            <AttendanceTable />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import AttendanceTable from './AttendanceTable.vue';

// Meta information
defineOptions({
  name: 'Attendance'
});
</script>

<style scoped>
.attendance-page {
  padding: 20px;
}
</style>