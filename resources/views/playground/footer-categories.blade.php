<!-- Footer Category -->
<div class="footer-category  modal-content-color">
	<div class="container">
		<div class="category-toggle">
			<a href="javascript:void(0);" class="toggle-btn">Books categories</a>
			<div class="toggle-items row book-grid-row">
				<div class="footer-col-book">
					<ul>
						@foreach($genres_array as $genre)
							<li><a href="{{route('playground.books-list', [$genre])}}">{{$genre}}</a></li>
						@endforeach
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- Footer Category End -->
