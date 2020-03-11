<header id="search-bar">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-8 col-md-offset-2">
				{if $user == 'admin'}
				<div class="row">
					<div class="col-sm-10">
						{/if}
						<div class="form-group">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-search"></i></span>
								<input type="text" id="search-input" name="search-input" class="form-control" placeholder="{ll key="checkin_placeholder_query"}">
								<div class="input-group-btn">
									<button type="button" class="btn btn-primary" id="search-btn">{ll key="checkin_label_search_btn"}</button>
								</div>
							</div>
						</div>
						{if $user == 'admin'}
					</div>
					<div class="col-sm-2">
						<div class="form-group">
							<button type="button" class="btn btn-success full-width" id="add-person-btn" data-target="#add-person" data-af-entry-id="0" data-af-table="ko_leute" data-af-mode="new">{ll key="checkin_label_add_new_person"}</button>
						</div>
					</div>
				</div>
				{/if}
			</div>
		</div>
	</div>
</header>
<header id="search-bar-placeholder">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-8 col-md-offset-2">
				{if $user == 'admin'}
				<div class="row">
					<div class="col-sm-10">
						{/if}
						<div class="form-group-placeholder"></div>
						{if $user == 'admin'}
					</div>
					<div class="col-sm-2">
						<div class="form-group-placeholder"></div>
					</div>
				</div>
				{/if}
			</div>
		</div>
	</div>
</header>
<div class="container-fluid">
	<div class="row">
		<div class="col-md-8 col-md-offset-2">
			<div id="notifications" name="notifications"></div>
			<div id="search-result-container" name="search-result-container">
				{if $results != ''}
					{$results}
				{else}
					<div class="panel panel-default">
						<div class="panel-body">
							{ll key="checkin_label_enter_query"}
						</div>
					</div>
				{/if}
			</div>
		</div>
	</div>
</div>
