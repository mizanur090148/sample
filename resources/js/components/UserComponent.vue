<template>
	<div id="user">
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
		                                <th scope="col" class="border-0">Action</th>
		                            </tr>
		                        </thead>
		                        <tbody>
									<tr v-show="users.length" v-for="(user, index) in users">
		                                <td>{{ ++index }}</td>
		                                <td>{{ user.name }}</td>	                                
		                                <td>
                                            <button type="button" class="btn btn-primary btn-sm" @click="edit(user)"><i class="fas fa-edit"></i></button>

                                            <button type="button" class="btn btn-danger btn-sm" @click="destroy(user.id)"><i class="fas fa-trash-alt"></i></button>
		                                </td>
	                              	</tr>
		                            <tr v-show="!users.length">
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
        <div class="modal fade" id="userModal" tabindex="-1" role="dialog" aria-labelledby="userModalTitle" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title font-weight-bold" id="userModalTitle">{{ editMode ? 'Edit' : 'Add New' }} User</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form @submit.prevent="editMode ? update() : store()">
                        <div class="modal-body">
                            <alert-error :form="form" class="text-center"></alert-error>
                            <div class="form-group">
                                <label class="font-weight-bold">Name</label>
                                <input v-model="form.name" type="text" name="name"
                                class="form-control" :class="{ 'is-invalid': form.errors.has('name') }">
                                <has-error :form="form" field="name"></has-error>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold">E-mail</label>
                                <input v-model="form.email" type="text" name="email"
                                class="form-control" :class="{ 'is-invalid': form.errors.has('email') }">
                                <has-error :form="form" field="email"></has-error>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold">Mobile No</label>
                                <input v-model="form.mobile_no" type="text" name="mobile_no"
                                class="form-control" :class="{ 'is-invalid': form.errors.has('mobile_no') }">
                                <has-error :form="form" field="mobile_no"></has-error>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold">Address</label>                              
                                <textarea v-model="form.address" name="address"
                                class="form-control" :class="{ 'is-invalid': form.errors.has('address') }"></textarea>
                                <has-error :form="form" field="address"></has-error>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold">Password</label>
                                <input v-model="form.password" type="text" name="password"
                                class="form-control" :class="{ 'is-invalid': form.errors.has('password') }">
                                <has-error :form="form" field="password"></has-error>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold">Confirm Password</label>
                                <input v-model="form.confirm_password" type="text" name="confirm_password"
                                class="form-control" :class="{ 'is-invalid': form.errors.has('confirm_password') }">
                                <has-error :form="form" field="confirm_password"></has-error>
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
            users: [],
            query: '',
            pagination: {
                current_page: 1
            },
            form: new Form({
                id: '',
                name: '',
                email: '',
                mobile_no: '',
                address: '',
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
            console.log('mounted');
            this.getData();
        },

        methods: {
         
            getData() {
                axios.get('api/users?page='+this.pagination.current_page)
                    .then(response => {
                        this.users = response.data.data;
                        this.pagination = response.data.meta;
                      
                    })
                    .catch(e => {
                        console.log(e);
                    })
            },
            searchData() {
                 axios.get('api/users?query='+this.query+'&page='+this.pagination.current_page)
                 .then(response => {
                        this.users = response.data.data;
                        this.pagination = response.data.meta;        
                    })
                    .catch(e => {
                        console.log(e);
                    })
            },            
            create() {
                this.editMode = false
                this.form.reset()
                this.form.clear()
                $('#userModal').modal('show');
            },
            store() {           
                this.form.busy = true;
                this.form.post('/api/users')
                    .then(response => {
                        this.getData();
                        $('#userModal').modal('hide');
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
            edit(user) {
                this.editMode = true
                this.form.reset()
                this.form.clear()
                this.form.fill(user)              
                $('#userModal').modal('show');
            },
            update() {
                this.form.busy = true;
                this.form.put('/api/users/'+this.form.id)
                    .then(response => {
                        this.getData();
                        $('#userModal').modal('hide');
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
            destroy(userId) {
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
                                    axios.delete('/api/users/'+userId)
                                        .then(response => {
                                            this.getData();
                                            this.$snotify.success('Successfully deleted', 'Success');
                                        })
                                        .catch(e => {
                                            this.$snotify.success('Not deleted', 'Fail');
                                        })
                                    console.log(user);
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

