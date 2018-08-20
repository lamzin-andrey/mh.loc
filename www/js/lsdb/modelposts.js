function ModelPosts(){
	ModelPosts.superclass.__construct.call(this);
	this.className = 'ModelPosts';
	//this.parentCall('__construct');
	this.table = 'posts';
	this.id = 0;
	this.title = "";
	this.body = "";
	this.du = "";
};
extend(Model, ModelPosts);
ModelPosts.prototype.parentCall = function(method) {
	window[this.className].superclass[method].call(this);
}


