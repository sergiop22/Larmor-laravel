@extends(Config::get('chatter.master_file_extend'))

@section(Config::get('chatter.yields.head'))
    <link href="/vendor/devdojo/chatter/assets/vendor/spectrum/spectrum.css" rel="stylesheet">
	<link href="/vendor/devdojo/chatter/assets/css/chatter.css" rel="stylesheet">
	<link href="/css/chatter2.css" rel="stylesheet" >
	@if($chatter_editor == 'simplemde')
		<link href="/vendor/devdojo/chatter/assets/css/simplemde.min.css" rel="stylesheet">
	@endif
@stop

@section('content')

<div id="chatter" class="chatter_home">

	<div id="chatter_hero2" >
		<div style= "background: rgba(0, 0, 0, 0.1); position: absolute; width: 100%; height: 100%; left: 0px; top: 0px;">
			<img src="{{ Config::get('chatter.headline_logo') }}" width="100" height="400">
		</div>
	</div>

	@if(Session::has('chatter_alert'))
		<div class="chatter-alert alert alert-{{ Session::get('chatter_alert_type') }}">
			<div class="container">
	        	<strong><i class="chatter-alert-{{ Session::get('chatter_alert_type') }}"></i> {{ Config::get('chatter.alert_messages.' . Session::get('chatter_alert_type')) }}</strong>
	        	{{ Session::get('chatter_alert') }}
	        	<i class="chatter-close"></i>
	        </div>
	    </div>
	    <div class="chatter-alert-spacer"></div>
	@endif

	@if (count($errors) > 0)
	    <div class="chatter-alert alert alert-danger">
	    	<div class="container">
	    		<p><strong><i class="chatter-alert-danger"></i> {{ Config::get('chatter.alert_messages.danger') }}</strong> Porfavor arregla los siguientes errores:</p>
		        <ul>
		            @foreach ($errors->all() as $error)
		                <li>{{ $error }}</li>
		            @endforeach
		        </ul>
		    </div>
	    </div>
	@endif

	<div class="container chatter_container">
		
	    <div class="row">

	    	<div class="col-md-3 left-column">
	    		<!-- SIDEBAR -->
	    		<div class="chatter_sidebar">
					<button class="btn btn-primary" id="new_discussion_btn"><i class="chatter-new"></i> Nueva {{ Config::get('chatter.titles.discussion') }}</button> 
					<a href="/{{ Config::get('chatter.routes.home') }}"><i class="chatter-bubble"></i> Todas las {{ Config::get('chatter.titles.discussions') }}</a>
					<ul class="nav nav-pills nav-stacked">
						<?php $categories = DevDojo\Chatter\Models\Models::category()->all(); ?>
						@foreach($categories as $category)
							<li><a href="/{{ Config::get('chatter.routes.home') }}/{{ Config::get('chatter.routes.category') }}/{{ $category->slug }}"><div class="chatter-box" style="background-color:{{ $category->color }}"></div> {{ $category->name }}</a></li>
						@endforeach
					</ul>
				</div>
				<!-- END SIDEBAR -->
	    	</div>
	        <div class="col-md-9 right-column">
	        	<div class="panel">
		        	<ul class="discussions">
		        		@foreach($discussions as $discussion)
				        	<li>
				        		<a class="discussion_list" href="/{{ Config::get('chatter.routes.home') }}/{{ Config::get('chatter.routes.discussion') }}/{{ $discussion->category->slug }}/{{ $discussion->slug }}">
					        		<div class="chatter_avatar">
					        			<?php $usuario = Auth::user()->avatar ?>
					        			<img src="/img/avatars/{{ $usuario }}" style="width: 80px; height: 80px; float: left; border-radius: 50%; margin-left: -10px; margin-top: -7px;">
					        		</div>

					        		<div class="chatter_middle">
					        			<h3 class="chatter_middle_title">{{ $discussion->title }} <div class="chatter_cat" style="background-color:{{ $discussion->category->color }}">{{ $discussion->category->name }}</div></h3>
					        			<span class="chatter_middle_details">Creado por: <span data-href="/user">{{ ucfirst($discussion->user->{Config::get('chatter.user.database_field_with_user_name')}) }}</span> {{ \Carbon\Carbon::createFromTimeStamp(strtotime($discussion->created_at))->diffForHumans() }}</span>
					        			@if($discussion->post[0]->markdown)
					        				<?php $discussion_body = GrahamCampbell\Markdown\Facades\Markdown::convertToHtml( $discussion->post[0]->body ); ?>
					        			@else
					        				<?php $discussion_body = $discussion->post[0]->body; ?>
					        			@endif
					        			<p>{{ substr(strip_tags($discussion_body), 0, 200) }}@if(strlen(strip_tags($discussion_body)) > 200){{ '...' }}@endif</p>
					        		</div>

					        		<div class="chatter_right">
					        			
					        			<div class="chatter_count"><i class="chatter-bubble"></i> {{ $discussion->postsCount[0]->total }}</div>
					        		</div>

					        		<div class="chatter_clear"></div>
					        	</a>
				        	</li>
			        	@endforeach
		        	</ul>
	        	</div>

	        	<div id="pagination">
	        		{{ $discussions->links() }}
	        	</div>

	        </div>
	    </div>
	</div>

	<div id="new_discussion">

    	<div class="chatter_loader dark" id="new_discussion_loader">
		    <div></div>
		</div>

    	<form id="chatter_form_editor" action="/{{ Config::get('chatter.routes.home') }}/{{ Config::get('chatter.routes.discussion') }}" method="POST">
        	<div class="row">
	        	<div class="col-md-7">
		        	<!-- TITLE -->
	                <input type="text" class="form-control" id="title" name="title" placeholder="Titulo de {{ Config::get('chatter.titles.discussion') }}" value="{{ old('title') }}" >
	            </div>

	            <div class="col-md-4">
		            <!-- CATEGORY -->
			            <select id="chatter_category_id" class="form-control" name="chatter_category_id">
			            	<option value="">Selecciona una categoria</option>
				            @foreach($categories as $category)
				            	@if(old('chatter_category_id') == $category->id)
				            		<option value="{{ $category->id }}" selected>{{ $category->name }}</option>
				            	@else
				            		<option value="{{ $category->id }}">{{ $category->name }}</option>
				            	@endif
				            @endforeach
			            </select>
		        </div>

		        <div class="col-md-1">
		        	<i class="chatter-close"></i>
		        </div>	
	        </div><!-- .row -->

            <!-- BODY -->
        	<div id="editor">
        		@if( $chatter_editor == 'tinymce' || empty($chatter_editor) )
					<label id="tinymce_placeholder">Agrega el contenido aquí </label>
    				<textarea id="body" class="richText" name="body" placeholder="">{{ old('body') }}</textarea>
    			@elseif($chatter_editor == 'simplemde')
    				<textarea id="simplemde" name="body" placeholder="">{{ old('body') }}</textarea>
    			@endif
    		</div>

            <input type="hidden" name="_token" id="csrf_token_field" value="{{ csrf_token() }}">

            <div id="new_discussion_footer">
            	<input type='text' id="color" name="color" /><span class="select_color_text">Selecciona un color (opcional)</span>
            	<button id="submit_discussion" class="btn btn-success pull-right"><i class="chatter-new"></i> Crear {{ Config::get('chatter.titles.discussion') }}</button>
            	<a href="/{{ Config::get('chatter.routes.home') }}" class="btn btn-default pull-right" id="cancel_discussion">Cancelar</a>
            	<div style="clear:both"></div>
            </div>
        </form>

    </div><!-- #new_discussion -->

</div>

@if( $chatter_editor == 'tinymce' || empty($chatter_editor) )
	<input type="hidden" id="chatter_tinymce_toolbar" value="{{ Config::get('chatter.tinymce.toolbar') }}">
	<input type="hidden" id="chatter_tinymce_plugins" value="{{ Config::get('chatter.tinymce.plugins') }}">
@endif
<input type="hidden" id="current_path" value="{{ Request::path() }}">

@endsection

@section(Config::get('chatter.yields.footer'))


@if( $chatter_editor == 'tinymce' || empty($chatter_editor) )
	<script src="/vendor/devdojo/chatter/assets/vendor/tinymce/tinymce.min.js"></script>
	<script src="/vendor/devdojo/chatter/assets/js/tinymce.js"></script>
	<script>
		var my_tinymce = tinyMCE;
		$('document').ready(function(){
			$('#tinymce_placeholder').click(function(){
				my_tinymce.activeEditor.focus();
			});
		});
	</script>
@elseif($chatter_editor == 'simplemde')
	<script src="/vendor/devdojo/chatter/assets/js/simplemde.min.js"></script>
	<script src="/vendor/devdojo/chatter/assets/js/chatter_simplemde.js"></script>
@endif

<script src="/vendor/devdojo/chatter/assets/vendor/spectrum/spectrum.js"></script>
<script src="/vendor/devdojo/chatter/assets/js/chatter.js"></script>
<script>
	$('document').ready(function(){

		$('.chatter-close').click(function(){
			$('#new_discussion').slideUp();
		});
		$('#new_discussion_btn, #cancel_discussion').click(function(){
			@if(Auth::guest())
				window.location.href = "/{{ Config::get('chatter.routes.home') }}/login";
			@else
				$('#new_discussion').slideDown();
				$('#title').focus();
			@endif
		});

		$("#color").spectrum({
		    color: "#333639",
		    preferredFormat: "hex",
		    containerClassName: 'chatter-color-picker',
		    cancelText: '',
    		chooseText: 'close',
		    move: function(color) {
				$("#color").val(color.toHexString());
			}
		});

		@if (count($errors) > 0)
			$('#new_discussion').slideDown();
			$('#title').focus();
		@endif


	});
</script>
@stop
