<template>
	<div id="factory">
	    <div class="page-header row no-gutters py-4">
	        <div class="col-12 col-sm-4 text-center text-sm-left mb-0">
	            <h3 class="page-title">User</h3>
	        </div>
	    </div>
	    <div class="row">
	        <div class="col">
	            <div class="card card-small mb-4">
	                <div class="card-header border-bottom">
	                	<div class="row">    
		                	<div class="col-md-3">			                    
			                    <button type="button" @click="create" class="btn btn-primary">Add New <i class="fas fa-plus"></i></button>
		                    </div>
		                    <div class="col-md-6"></div>
		                    <div class="col-md-3">
	                            <input type="text" v-model="query" class="form-control" placeholder="Search"/>
	                        </div>
	                    </div>
	                </div>
	               
	                <div class="card-body p-0 pb-3 text-center">
	                	<div class="table-responsive">
		                    <table class="table table-sm">
		                        <thead class="bg-light">
		                            <tr>
		                                <th scope="col" class="border-0">#</th>
		                                <th scope="col" class="border-0">Name</th>
                                        <th scope="col" class="border-0">E-mail</th>
                                        <th scope="col" class="border-0">Phone No.</th>
                                        <th scope="col" class="border-0">Address</th>
                                        <th scope="col" class="border-0">Role</th>
		                                <th scope="col" class="border-0">Action</th>
		                            </tr>
		                        </thead>
		                        <tbody>
									<tr v-show="factories.length" v-for="(factory, index) in factories">
		                                <td>{{ ++index }}</td>
		                                <td>{{ factory.name }}</td>
                                        <td>{{ factory.email }}</td>
                                        <td>{{ factory.phone_no }}</td>
                                        <td>{{ factory.address }}</td>
                                        <td>{{ factory.role ? factory.role.name : '' }}</td>
		                                <td>
                                            <button type="button" class="btn btn-primary btn-sm" @click="edit(factory)"><i class="fas fa-edit"></i></button>

                                            <button type="button" class="btn btn-danger btn-sm" @click="destroy(factory.id)"><i class="fas fa-trash-alt"></i></button>
		                                </td>
	                              	</tr>
		                            <tr v-show="!factories.length">
		                                <td colspan="7">
		                                    <div class="alert alert-danger text-center" role="danger">Sorry!! Data not found</div>
		                                </td>
		                            </tr>
								</tbody>
		                    </table>
	                    </div>
                        <pagination v-if="pagination.last_page > 1" 
                          :pagination="pagination" :offset="5" @paginate="query === '' ? getData() : searchData()"></pagination>
	                </div>
	            </div>
	        </div>
	    </div>
	    <!-- Create and Edit Modal -->
        <div class="modal fade" id="factoryModal" tabindex="-1" role="dialog" aria-labelledby="factoryModalTitle" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title font-weight-bold" id="factoryModalTitle">{{ editMode ? 'Edit' : 'Add New' }} User</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form @submit.prevent="editMode ? update() : store()">
                        <div class="modal-body">
                            <div class="form-group row">
                                <alert-error :form="form" class="text-center"></alert-error>
                                <div class="col-sm-6">
                                    <label class="font-weight-bold">Name</label>
                                    <input v-model="form.name" type="text" name="name"
                                    class="form-control" :class="{ 'is-invalid': form.errors.has('name') }">
                                    <has-error :form="form" field="name"></has-error>
                                </div>
                                <div class="col-sm-6">
                                    <label class="font-weight-bold">E-mail</label>
                                    <input v-model="form.email" type="text" name="email"
                                    class="form-control" :class="{ 'is-invalid': form.errors.has('email') }">
                                    <has-error :form="form" field="email"></has-error>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-6">
                                    <label class="font-weight-bold">Mobile No</label>
                                    <input v-model="form.mobile_no" type="text" name="mobile_no"
                                    class="form-control" :class="{ 'is-invalid': form.errors.has('mobile_no') }">
                                    <has-error :form="form" field="mobile_no"></has-error>
                                </div>
                                <div class="col-sm-6">
                                    <label class="font-weight-bold">Address</label>                              
                                    <textarea v-model="form.address" name="address"
                                    class="form-control" :class="{ 'is-invalid': form.errors.has('address') }"></textarea>
                                    <has-error :form="form" field="address"></has-error>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-6">
                                    <label class="font-weight-bold">Role1234</label>
                                    <select class='form-control' v-model='form.role_id'>
                                      <option value=''>Select Role</option>
                                      <option v-for='role in roles' :value='role.id'>{{ role.name }}</option>
                                    </select>
                                    <has-error :form="form" field="role_id"></has-error>
                                </div>
                                <div class="col-sm-6">
                                    <label class="font-weight-bold">Status</label>
                                    <select class='form-control' v-model='form.status'>
                                      <option value='1'>Active</option>
                                      <option value='0'>Inactive</option>
                                    </select>
                                    <has-error :form="form" field="status"></has-error>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-6">
                                    <label class="font-weight-bold">Factory</label>
                                    <select class='form-control' v-model='form.factory_id'>
                                      <option value=''>Select Factory</option>
                                      <option v-for='factory in factories_dropdown' :value='factory.id'>{{ factory.name }}</option>
                                    </select>
                                    <has-error :form="form" field="factory_id"></has-error>
                                </div>
                            </div>
                            <div class="form-group row">
                                 <div class="col-sm-6">
                                    <label class="font-weight-bold">Password</label>
                                    <input v-model="form.password" type="password" name="password"
                                    class="form-control" :class="{ 'is-invalid': form.errors.has('password') }">
                                    <has-error :form="form" field="password"></has-error>
                                </div>
                                <div class="col-sm-6">
                                    <label class="font-weight-bold">Confirm Password</label>
                                    <input v-model="form.confirm_password" type="password" name="confirm_password"
                                    class="form-control" :class="{ 'is-invalid': form.errors.has('confirm_password') }">
                                    <has-error :form="form" field="confirm_password"></has-error>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button :disabled="form.busy" type="submit" class="btn btn-primary">{{ editMode ? 'Update' : 'Save' }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
	    <vue-snotify></vue-snotify>
	</div>
</template>

<script>
    export default {
        data () {
          return {
          	editMode: false,
            factories_dropdown: [],
            factories: [],
            roles: [],
            query: '',
            //role_id: '',
            //status: 1,
            pagination: {
                current_page: 1
            },
            form: new Form({
                id: '',
                name: '',
                email: '',
                mobile_no: '',
                address: '',
                factory_id: '',
                role_id: '',
                status: 1,
                password: ''
            }),
          }
        }, 

        watch: {
            query: function(newQ,old) {
                if(newQ === '') {
                    this.getData();
                } else {
                    this.searchData();
                }
            }
        },      

        mounted() {
            this.getData();
        },

        methods: {
         
            getData() {
                axios.get('api/factories?page='+this.pagination.current_page)
                    .then(response => {
                        this.factories = response.data.data;
                        this.pagination = response.data.meta;
                      
                    })
                    .catch(e => {
                        console.log(e);
                    })
            },
            searchData() {
                 axios.get('api/factories?query='+this.query+'&page='+this.pagination.current_page)
                 .then(response => {
                        this.factories = response.data.data;
                        this.pagination = response.data.meta;        
                    })
                    .catch(e => {
                        console.log(e);
                    })
            },            
            create() {
                this.editMode = false
                this.form.reset();
                this.form.clear();
                axios.get('api/roles')
                    .then(response => {
                        this.roles = response.data.data;
                    })
                    .catch(e => {
                        console.log(e);
                    })
                axios.get('api/factories-dropdown')
                    .then(response => {
                        this.factories_dropdown = response.data.data;
                    })
                    .catch(e => {
                        console.log(e);
                    })
                $('#factoryModal').modal('show');
            },
            store() {
                this.form.busy = true;
                this.form.post('/api/factories')
                    .then(response => {
                        this.getData();
                        $('#factoryModal').modal('hide');
                        if (this.form.successful) {
                            this.$snotify.success('Successfully created', 'Success');
                            this.form.reset();
                            this.form.clear();
                        } else {
                            //this.$snotify.error('Something went worng', 'error');
                        }
                    })
                    .catch(e => {                    	
                        console.log(e);
                    })
            },            
            edit(factory) {
                this.editMode = true
                this.form.reset()
                this.form.clear()
                this.form.fill(factory)              
                $('#factoryModal').modal('show');
            },
            update() {
                this.form.busy = true;
                this.form.put('/api/factories/'+this.form.id)
                    .then(response => {
                        this.getData();
                        $('#factoryModal').modal('hide');
                        if (this.form.successful) {
                            this.$snotify.success('Successfully updated', 'Success');
                            this.form.reset();
                            this.form.clear();
                        } else {
                            this.$snotify.error('Something went worng', 'error');
                        }
                    })
                    .catch(e => {
                        console.log(e);
                    })
            },
            destroy(factoryId) {
                this.$snotify.clear();
                this.$snotify.confirm(
                    "Are you sure?",
                    {
                        closeOnClick: false,
                        pauseOnHover: true,
                        buttons: [
                            {
                                text: "Yes",
                                action: toast => {
                                    this.$snotify.remove(toast.id);
                                    axios.delete('/api/factories/'+factoryId)
                                        .then(response => {
                                            this.getData();
                                            this.$snotify.success('Successfully deleted', 'Success');
                                        })
                                        .catch(e => {
                                            this.$snotify.success('Not deleted', 'Fail');
                                        })
                                    console.log(factory);
                                },
                                bold: true
                            },
                            {
                                text: "No",
                                action: toast => {
                                    this.$snotify.remove(toast.id);
                                },
                                bold: true
                            }
                        ]
                    }
                );
            }
        }
    }
</script>

