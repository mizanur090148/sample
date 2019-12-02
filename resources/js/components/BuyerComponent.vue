<template>
	<div id="buyer">
	    <div class="page-header row no-gutters py-4">
	        <div class="col-12 col-sm-4 text-center text-sm-left mb-0">
	            <h3 class="page-title">Buyer</h3>
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
									<tr v-show="buyers.length" v-for="(buyer, index) in buyers">
		                                <td>{{ ++index }}</td>
		                                <td>{{ buyer.name }}</td>	                                
		                                <td>
                                            <button type="button" class="btn btn-primary btn-sm" @click="edit(buyer)"><i class="fas fa-edit"></i></button>

                                            <button type="button" class="btn btn-danger btn-sm" @click="destroy(buyer.id)"><i class="fas fa-trash-alt"></i></button>
		                                </td>
	                              	</tr>
		                            <tr v-show="!buyers.length">
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
        <div class="modal fade" id="buyerModal" tabindex="-1" role="dialog" aria-labelledby="buyerModalTitle" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title font-weight-bold" id="buyerModalTitle">{{ editMode ? 'Edit' : 'Add New' }} Buyer</h5>
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
            buyers: [],
            query: '',
            pagination: {
                current_page: 1
            },
            form: new Form({
                id: '',
                name: ''               
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
                axios.get('api/buyers?page='+this.pagination.current_page)
                    .then(response => {
                        this.buyers = response.data.data;
                        this.pagination = response.data.meta;
                      
                    })
                    .catch(e => {
                        console.log(e);
                    })
            },
            searchData() {
                 axios.get('api/buyers?query='+this.query+'&page='+this.pagination.current_page)
                 .then(response => {
                        this.buyers = response.data.data;
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
                $('#buyerModal').modal('show');
            },
            store() {           
                this.form.busy = true;
                this.form.post('/api/buyers')
                    .then(response => {
                        this.getData();
                        $('#buyerModal').modal('hide');
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
            edit(buyer) {
                this.editMode = true
                this.form.reset()
                this.form.clear()
                this.form.fill(buyer)              
                $('#buyerModal').modal('show');
            },
            update() {
                this.form.busy = true;
                this.form.put('/api/buyers/'+this.form.id)
                    .then(response => {
                        this.getData();
                        $('#buyerModal').modal('hide');
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
            destroy(buyerId) {
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
                                    axios.delete('/api/buyers/'+buyerId)
                                        .then(response => {
                                            this.getData();
                                            this.$snotify.success('Successfully deleted', 'Success');
                                        })
                                        .catch(e => {
                                            this.$snotify.success('Not deleted', 'Fail');
                                        })
                                    console.log(buyer);
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

