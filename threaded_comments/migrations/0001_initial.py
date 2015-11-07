# -*- coding: utf-8 -*-
from __future__ import unicode_literals

from django.db import models, migrations
import mptt.fields
import django.db.models.deletion
from django.conf import settings
import rich_editor.fields


class Migration(migrations.Migration):

	dependencies = [
		('contenttypes', '0002_remove_content_type_name'),
		migrations.swappable_dependency(settings.AUTH_USER_MODEL),
	]

	operations = [
		migrations.CreateModel(
			name='Comment',
			fields=[
				('id', models.AutoField(verbose_name='ID', serialize=False, auto_created=True, primary_key=True)),
				('object_id', models.TextField(verbose_name='ID objektu')),
				('subject', models.CharField(max_length=100, verbose_name='predmet')),
				('user_name', models.CharField(max_length=50, verbose_name='pou\u017e\xedvate\u013esk\xe9 meno', blank=True)),
				('original_comment', rich_editor.fields.RichTextOriginalField(verbose_name='obsah', property_name='comment', filtered_field='filtered_comment')),
				('filtered_comment', rich_editor.fields.RichTextFilteredField(editable=False)),
				('submit_date', models.DateTimeField(default=None, verbose_name='d\xe1tum odoslania')),
				('ip_address', models.GenericIPAddressField(null=True, verbose_name='IP adresa', blank=True)),
				('is_public', models.BooleanField(default=True, verbose_name='verejn\xfd')),
				('is_removed', models.BooleanField(default=False, verbose_name='odstr\xe1nen\xfd')),
				('is_locked', models.BooleanField(default=False, verbose_name='uzamknut\xfd')),
				('updated', models.DateTimeField(verbose_name='naposledy upraven\xe9', editable=False)),
				('lft', models.PositiveIntegerField(editable=False, db_index=True)),
				('rght', models.PositiveIntegerField(editable=False, db_index=True)),
				('tree_id', models.PositiveIntegerField(editable=False, db_index=True)),
				('level', models.PositiveIntegerField(editable=False, db_index=True)),
				('content_type', models.ForeignKey(related_name='content_type_set_for_comment', verbose_name='typ obsahu', to='contenttypes.ContentType')),
				('parent', mptt.fields.TreeForeignKey(related_name='children', verbose_name='nadraden\xfd', blank=True, to='threaded_comments.Comment', null=True)),
				('user', models.ForeignKey(related_name='comment_comments', on_delete=django.db.models.deletion.SET_NULL, verbose_name='pou\u017e\xedvate\u013e', blank=True, to=settings.AUTH_USER_MODEL, null=True)),
			],
			options={
				'ordering': ('tree_id', 'lft'),
				'db_table': 'django_comments',
				'verbose_name': 'koment\xe1r',
				'verbose_name_plural': 'koment\xe1re',
			},
		),
		migrations.CreateModel(
			name='CommentFlag',
			fields=[
				('id', models.AutoField(verbose_name='ID', serialize=False, auto_created=True, primary_key=True)),
				('flag', models.CharField(max_length=30, verbose_name='zna\u010dka', db_index=True)),
				('flag_date', models.DateTimeField(default=None, verbose_name='d\xe1tum')),
				('comment', models.ForeignKey(related_name='flags', verbose_name='koment\xe1r', to='threaded_comments.Comment')),
				('user', models.ForeignKey(related_name='threadedcomment_flags', verbose_name='pou\u017e\xedvate\u013e', to=settings.AUTH_USER_MODEL)),
			],
			options={
				'db_table': 'django_comment_flags',
				'verbose_name': 'zna\u010dka komen\xe1ra',
				'verbose_name_plural': 'zna\u010dky koment\xe1rov',
			},
		),
		migrations.CreateModel(
			name='RootHeader',
			fields=[
				('id', models.AutoField(verbose_name='ID', serialize=False, auto_created=True, primary_key=True)),
				('pub_date', models.DateTimeField(db_index=True)),
				('last_comment', models.DateTimeField(db_index=True)),
				('comment_count', models.PositiveIntegerField(default=0, db_index=True)),
				('is_locked', models.BooleanField(default=False)),
				('object_id', models.PositiveIntegerField()),
				('content_type', models.ForeignKey(to='contenttypes.ContentType')),
			],
			options={
				'verbose_name': 'diskusia',
				'verbose_name_plural': 'diskusie',
			},
		),
		migrations.CreateModel(
			name='UserDiscussionAttribute',
			fields=[
				('id', models.AutoField(verbose_name='ID', serialize=False, auto_created=True, primary_key=True)),
				('time', models.DateTimeField(null=True, blank=True)),
				('watch', models.BooleanField(default=False)),
				('discussion', models.ForeignKey(to='threaded_comments.RootHeader')),
				('user', models.ForeignKey(to=settings.AUTH_USER_MODEL)),
			],
		),
		migrations.AlterUniqueTogether(
			name='userdiscussionattribute',
			unique_together=set([('user', 'discussion')]),
		),
		migrations.AlterUniqueTogether(
			name='rootheader',
			unique_together=set([('content_type', 'object_id')]),
		),
		migrations.AlterUniqueTogether(
			name='commentflag',
			unique_together=set([('user', 'comment', 'flag')]),
		),
		migrations.AlterIndexTogether(
			name='comment',
			index_together=set([('object_id', 'content_type')]),
		),
	]
