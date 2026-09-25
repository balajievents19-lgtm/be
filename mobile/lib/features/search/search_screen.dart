import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../core/l10n/locale_controller.dart';
import '../../core/network/api_exception.dart';
import '../../data/providers.dart';
import '../../data/search_index.dart';
import '../../widgets/common.dart';

class SearchScreen extends ConsumerStatefulWidget {
  const SearchScreen({super.key});

  @override
  ConsumerState<SearchScreen> createState() => _SearchScreenState();
}

class _SearchScreenState extends ConsumerState<SearchScreen> {
  final _query = TextEditingController();
  final _debouncer = Debouncer();
  List<SearchHit> _hits = const [];
  bool _loading = false;
  String? _error;

  @override
  void dispose() {
    _debouncer.dispose();
    _query.dispose();
    super.dispose();
  }

  void _onChanged(String value) {
    _debouncer.run(() async {
      if (value.trim().length < 2) {
        setState(() {
          _hits = const [];
          _loading = false;
          _error = null;
        });
        return;
      }
      setState(() {
        _loading = true;
        _error = null;
      });
      try {
        final hits = await SearchIndex(ref.read(contentRepositoryProvider)).query(value);
        if (mounted) {
          setState(() {
            _hits = hits;
            _loading = false;
          });
        }
      } on ApiException catch (e) {
        if (mounted) {
          setState(() {
            _error = e.message;
            _loading = false;
          });
        }
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final t = ref.watch(stringsProvider);
    final grouped = <String, List<SearchHit>>{};
    for (final hit in _hits) {
      grouped.putIfAbsent(hit.kind, () => []).add(hit);
    }
    return Scaffold(
      appBar: AppBar(title: Text(t.get('search'))),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: TextField(
              controller: _query,
              onChanged: _onChanged,
              decoration: InputDecoration(prefixIcon: const Icon(Icons.search), hintText: t.get('search')),
            ),
          ),
          if (_loading) const LinearProgressIndicator(),
          Expanded(
            child: _error != null
                ? ErrorState(message: _error!, onRetry: () => _onChanged(_query.text))
                : _hits.isEmpty && _query.text.trim().length >= 2 && !_loading
                    ? EmptyState(message: t.get('noResults'))
                    : ListView(
                        children: grouped.entries.expand((entry) {
                          return [
                            Padding(
                              padding: const EdgeInsets.fromLTRB(16, 12, 16, 4),
                              child: Text(entry.key, style: Theme.of(context).textTheme.titleMedium),
                            ),
                            ...entry.value.map(
                              (hit) => ListTile(
                                title: Text(hit.title),
                                subtitle: hit.subtitle == null ? null : Text(hit.subtitle!, maxLines: 2, overflow: TextOverflow.ellipsis),
                                onTap: () => context.push(hit.route),
                              ),
                            ),
                          ];
                        }).toList(),
                      ),
          ),
        ],
      ),
    );
  }
}
