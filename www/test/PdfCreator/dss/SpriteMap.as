package drake.dss
	{
	import flash.display.*
	class SpriteMap
	{
		private var keys :Array;
		private var items:Array;
		public function SpriteMap ()
		{
			keys = new Array();
			items = new Array();
			
		}
		//---------------------------------
		public function getSprite(key:String)
		{
	//	trace ("keys.length == " + keys.length);
			var i = this.indexOf(key);
			if (i > -1) return items[i];
			i = keys.length;
			//if (key == "second") trace ("keys.length == " + i);
			keys.push(key);
			items.push(new Sprite());
			return items[i];
		}
		//---------------------------------
		public function indexOf(key:String):Number
		{
			var i:Number = 0;
			while (i < keys.length)
			{
			 if (keys[i] == key) return i;
				i++;
			}
			return -1;
		}
		//----------------------------------
		public function getAllSprites():Array
		{
			return items;
		}
		//----------------------------------
		public function getKeys():Array
		{
			return keys;
		}
	}
}